<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%products}}`.
 */
class m250120_160942_create_products_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%products}}', [
            'id' => $this->primaryKey(),
            'category_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
        ]);

        $this->createIndex('categories_name', '{{%products}}', ['category_id', 'name'], true);

        $this->addForeignKey('fk-products-category_id', '{{%products}}', 'category_id', '{{%categories}}', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-products-category_id', '{{%products}}');

        $this->dropTable('{{%products}}');
    }
}
