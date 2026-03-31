<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%service_item}}`.
 */
class m260304_021930_create_service_item_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%service_item}}', [
            'id' => $this->primaryKey(),
            'service_id' => $this->integer(),
            'name' => $this->string(100)->notNull(),
            'serial' => $this->string(50)->notNull(),
            'unit' => $this->string(20)->notNull(),
            'quantity' => $this->integer()->notNull()->defaultValue(1),
            'description' => $this->text()->notNull(),
            'discount_type' => $this->string(10)->notNull(),
            'discount' => $this->decimal(10, 2)->notNull()->defaultValue(0),
            'full_price' => $this->decimal(10, 2)->notNull()->defaultValue(0),
            'cost' => $this->decimal(10, 2)->notNull()->defaultValue(0),
            'price' => $this->decimal(10, 2)->notNull()->defaultValue(0),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%service_item}}');
    }
}
