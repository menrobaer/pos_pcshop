<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%activity_log}}`.
 */
class m260210_054622_create_activity_log_table extends Migration
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
      '{{%activity_log}}',
      [
        'id' => $this->primaryKey(),
        'user_id' => $this->integer(),
        'controller' => $this->string(100),
        'action' => $this->string(100),
        'method' => $this->string(10),
        'params' => $this->text(),
        'ip_address' => $this->string(45),
        'user_agent' => $this->string(),
        'created_at' => $this->dateTime()->notNull(),
      ],
      $tableOptions,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->dropTable('{{%activity_log}}');
  }
}
