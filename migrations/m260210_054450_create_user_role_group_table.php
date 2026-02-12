<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_role_group}}`.
 */
class m260210_054450_create_user_role_group_table extends Migration
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
      '{{%user_role_group}}',
      [
        'id' => $this->primaryKey(),
        'name' => $this->string(50)->notNull(),
      ],
      $tableOptions,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->dropTable('{{%user_role_group}}');
  }
}
