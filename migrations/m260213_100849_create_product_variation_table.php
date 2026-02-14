<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%product_variation}}`.
 */
class m260213_100849_create_product_variation_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%product_variation}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull(),
            'serial' => $this->string(50)->notNull(),
            'cost' => $this->decimal(10, 2)->defaultValue(0.00),
            'created_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_at' => $this->dateTime(),
            'updated_by' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%product_variation}}');
    }
}
