<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%expense_item}}`.
 */
class m260210_053924_create_expense_item_table extends Migration
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
      '{{%expense_item}}',
      [
        'id' => $this->primaryKey(),
        'expense_id' => $this->integer(),
        'title' => $this->string(100)->notNull(),
        'quantity' => $this->integer()->notNull()->defaultValue(1),
        'description' => $this->text()->notNull(),
        'price' => $this->decimal(10, 2)->notNull()->defaultValue(0),
      ],
      $tableOptions,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->dropTable('{{%expense_item}}');
  }
}
