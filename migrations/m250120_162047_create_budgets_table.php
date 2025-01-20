<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%budgets}}`.
 */
class m250120_162047_create_budgets_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%budgets}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull(),
            'period' => $this->date()->notNull(),
            'value' => $this->float()->notNull(),
        ]);

        $this->createIndex('budgets-period', '{{%budgets}}', 'period');

        $this->createIndex('budgets-product_id-period', '{{%budgets}}', ['product_id', 'period'], true);

        $this->addForeignKey('fk-budgets-product_id', '{{%budgets}}', 'product_id', '{{%products}}', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-products-category_id', '{{%products}}');

        $this->dropTable('{{%budgets}}');
    }
}
