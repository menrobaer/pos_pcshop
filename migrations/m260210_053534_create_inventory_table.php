<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%inventory}}`.
 */
class m260210_053534_create_inventory_table extends Migration
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
      '{{%inventory}}',
      [
        'id' => $this->primaryKey(),
        'product_id' => $this->integer()->notNull(),
        'type' => $this->string(10)->notNull(),
        'transaction_id' => $this->integer()->notNull(),
        'in' => $this->integer()->notNull()->defaultValue(0),
        'out' => $this->integer()->notNull()->defaultValue(0),
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
    $this->dropTable('{{%inventory}}');
  }
}
