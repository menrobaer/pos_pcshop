<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%invoice}}`.
 */
class m260210_051728_create_invoice_table extends Migration
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
      '{{%invoice}}',
      [
        'id' => $this->primaryKey(),
        'quotation_id' => $this->integer()->notNull(),
        'code' => $this->string(50)->notNull(),
        'serial_code' => $this->string(50)->notNull(),
        'customer_id' => $this->integer()->notNull(),
        'phone' => $this->string(50)->notNull(),
        'address' => $this->string(50)->notNull(),
        'date' => $this->date()->notNull(),
        'due_date' => $this->date()->notNull(),
        'remark' => $this->text()->notNull(),
        'delivery_fee' => $this->decimal(10, 2)->notNull()->defaultValue(0),
        'extra_charge' => $this->decimal(10, 2)->notNull()->defaultValue(0),
        'discount_amount' => $this->decimal(10, 2)->notNull()->defaultValue(0),
        'cost_total' => $this->decimal(10, 2)->notNull()->defaultValue(0),
        'sub_total' => $this->decimal(10, 2)->notNull()->defaultValue(0),
        'grand_total' => $this->decimal(10, 2)->notNull()->defaultValue(0),
        'paid_amount' => $this->decimal(10, 2)->notNull()->defaultValue(0),
        'balance_amount' => $this->decimal(10, 2)->notNull()->defaultValue(0),
        'status' => $this->smallInteger()->notNull()->defaultValue(1),
        'created_at' => $this->dateTime()->notNull(),
        'created_by' => $this->integer(),
        'updated_at' => $this->dateTime(),
        'updated_by' => $this->integer(),
      ],
      $tableOptions,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->dropTable('{{%invoice}}');
  }
}
