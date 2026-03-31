<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%service_payment}}`.
 */
class m260304_034236_create_service_payment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%service_payment}}', [
            'id' => $this->primaryKey(),
            'service_id' => $this->integer()->notNull(),
            'payment_method_id' => $this->integer()->notNull(),
            'code' => $this->string(50)->notNull(),
            'date' => $this->date()->notNull(),
            'remark' => $this->text(),
            'amount' => $this->decimal(10, 2)->notNull()->defaultValue(0),
            'status' => $this->smallInteger()->notNull()->defaultValue(1),
            'created_at' => $this->dateTime()->notNull(),
            'created_by' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%service_payment}}');
    }
}
