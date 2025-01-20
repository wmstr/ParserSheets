<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "budgets".
 *
 * @property int $id
 * @property int $product_id
 * @property string $period
 * @property float $value
 *
 * @property Product $product
 */
class Budget extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'budgets';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'period', 'value'], 'required'],
            [['product_id'], 'integer'],
            [['period'], 'safe'],
            [['value'], 'number'],
            [['product_id', 'period'], 'unique', 'targetAttribute' => ['product_id', 'period']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Продукт',
            'period' => 'Период',
            'value' => 'Бюджет',
        ];
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }
}
