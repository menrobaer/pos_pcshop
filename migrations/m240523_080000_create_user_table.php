<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user}}`.
 */
class m240523_080000_create_user_table extends Migration
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
      '{{%user}}',
      [
        'id' => $this->primaryKey(),
        'first_name' => $this->string()->notNull(),
        'last_name' => $this->string()->notNull(),
        'role_id' => $this->integer()->notNull(),
        'image' => $this->string()->defaultValue(null),
        'username' => $this->string()->notNull()->unique(),
        'auth_key' => $this->string(32)->notNull(),
        'password_hash' => $this->string()->notNull(),
        'password_reset_token' => $this->string()->unique(),
        'email' => $this->string()->notNull()->unique(),
        'status' => $this->smallInteger()->notNull()->defaultValue(1),
        'created_at' => $this->integer()->notNull(),
        'updated_at' => $this->integer()->notNull(),
        'verification_token' => $this->string()->defaultValue(null),
      ],
      $tableOptions,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->dropTable('{{%user}}');
  }
}
