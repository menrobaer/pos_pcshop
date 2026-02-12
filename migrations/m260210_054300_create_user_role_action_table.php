<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_role_action}}`.
 */
class m260210_054300_create_user_role_action_table extends Migration
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
      '{{%user_role_action}}',
      [
        'id' => $this->primaryKey(),
        'group_id' => $this->integer(),
        'name' => $this->string(50)->notNull(),
        'controller' => $this->string(50)->notNull(),
        'action' => $this->text()->notNull(),
        'status' => $this->smallInteger()->notNull()->defaultValue(1),
      ],
      $tableOptions,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->dropTable('{{%user_role_action}}');
  }
}
