<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\Budget;
use app\models\Category;
use app\models\Product;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Парсер таблиц гугл
 *
 * @author Alexey Garkin <wmstr2007@gmail.com>
 * @since 1.0
 */
class ParserController extends Controller
{
    /**
     * Парсит таблицу гугл
     * @return int Exit code
     */
    public function actionIndex()
    {
        $spreadsheetId = \Yii::$app->params['spreadsheetId'];

        $client = new \Google_Client();
        $client->setApplicationName("ParserSheets");
        $client->setDeveloperKey(\Yii::$app->params['api']['apiKey']);

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $spreadsheets = new \Google_Service_Sheets($client);
            $spreadsheet = $spreadsheets->spreadsheets->get($spreadsheetId, ['ranges' => 'MA', 'includeGridData' => true]);

            $year = preg_replace('/[^0-9]/', '', $spreadsheet->getProperties()->getTitle());

            /**@var $sheet \Google\Service\Sheets\Sheet */
            $sheet = $spreadsheet->sheets[0];

            /**@var $gridData \Google\Service\Sheets\GridData */
            $gridData = $sheet->getData()[0];

            $index = 0;
            $categoryID = null;
            $rowsData = $gridData->getRowData();
            foreach ($rowsData as $key => $row) {
                if ($key < 3) continue;

                /**@var $row \Google\Service\Sheets\RowData */
                $cells = $row->getValues();

                $name = $cells[0]->getFormattedValue();

                if (!$name) continue;
                if ($name == 'CO-OP') break;
                if ($name == 'Total') continue;

                if ($cells[0]->getEffectiveFormat()->getTextFormat()->getBold()) {
                    //Сохраняем категорию если такой нет в базе
                    $category = Category::findOne(['name' => $name]);
                    if (!$category) {
                        $category = new Category([
                            'name' => $name,
                        ]);
                        $category->save();
                    }

                    $categoryID = $category->id;
                    echo ++$index.'. '.$name,PHP_EOL;
                } else {
                    //Выбираем значения по месяцам для продукта
                    $values = [];
                    for ($i = 1; $i < 13; $i++)
                        $values[] = $cells[$i]->getEffectiveValue() ? $cells[$i]->getEffectiveValue()->getNumberValue() : 0;

                    //Сохраняем продукт если его нет в базе
                    $product = Product::findOne(['name' => $name, 'category_id' => $categoryID]);
                    if (!$product) {
                        $product = new Product([
                            'name' => $name,
                            'category_id' => $categoryID
                        ]);
                        $product->save();
                    }

                    foreach ($values as $month => $value) {
                        //Сохраняем бюджет для продукта на период
                        $date = sprintf("%d-%'.02d-01", $year, $month + 1);
                        $budget = Budget::findOne(['product_id' => $product->id, 'period' => $date]);

                        if ($budget && !$value) {
                            $budget->delete();
                            continue;
                        } else if (!$value) {
                            continue;
                        }

                        if (!$budget) {
                            $budget = new Budget([
                                'product_id' => $product->id,
                                'period' => $date
                            ]);
                        }
                        $budget->value = $value;
                        $budget->save();
                    }

                    echo $name.': '.implode(', ', $values).PHP_EOL;
                }
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            echo $e->getMessage();
            return ExitCode::IOERR;
        }

        return ExitCode::OK;
    }
}
