<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%customer}}`.
 */
class m260210_050628_create_customer_table extends Migration
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
      '{{%customer}}',
      [
        'id' => $this->primaryKey(),
        'name' => $this->string(50)->notNull(),
        'phone' => $this->string(50),
        'address' => $this->string(),
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
    $this->dropTable('{{%customer}}');
  }
}
