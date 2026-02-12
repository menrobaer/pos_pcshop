<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%purchase_order_payment}}`.
 */
class m260212_073820_create_purchase_order_payment_table extends Migration
{
  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $tableOptions = null;
    if ($this->db->driverName === 'mysql') {
      $tableOptions =
        'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
    }

    $this->createTable(
      '{{%purchase_order_payment}}',
      [
        'id' => $this->primaryKey(),
        'purchase_order_id' => $this->integer()->notNull(),
        'payment_method_id' => $this->integer()->notNull(),
        'code' => $this->string(50)->notNull(),
        'date' => $this->date()->notNull(),
        'remark' => $this->text(),
        'amount' => $this->decimal(10, 2)->notNull()->defaultValue(0),
        'status' => $this->smallInteger()->notNull()->defaultValue(1),
        'created_at' => $this->dateTime()->notNull(),
        'created_by' => $this->integer(),
      ],
      $tableOptions,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->dropTable('{{%purchase_order_payment}}');
  }
}
