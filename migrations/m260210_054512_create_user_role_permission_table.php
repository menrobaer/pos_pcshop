<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_role_permission}}`.
 */
class m260210_054512_create_user_role_permission_table extends Migration
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
      '{{%user_role_permission}}',
      [
        'id' => $this->primaryKey(),
        'user_role_id' => $this->integer(),
        'action_id' => $this->integer(),
      ],
      $tableOptions,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->dropTable('{{%user_role_permission}}');
  }
}
