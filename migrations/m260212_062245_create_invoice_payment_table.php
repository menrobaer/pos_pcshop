<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%invoice_payment}}`.
 */
class m260212_062245_create_invoice_payment_table extends Migration
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
      '{{%invoice_payment}}',
      [
        'id' => $this->primaryKey(),
        'invoice_id' => $this->integer()->notNull(),
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
    $this->dropTable('{{%invoice_payment}}');
  }
}
