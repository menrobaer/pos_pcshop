<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%product_model}}`.
 */
class m260213_072715_create_product_model_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%product_model}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(1),
            'created_at' => $this->dateTime()->notNull(),
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
        $this->dropTable('{{%product_model}}');
    }
}
