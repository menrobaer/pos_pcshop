<?php

use yii\db\Migration;

/**
 * Class m240524_000000_seed_users
 */
class m240524_000000_seed_users extends Migration
{
  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $security = Yii::$app->security;
    $password = '123456';
    $time = time();

    $users = [
      [
        'first_name' => 'Alice',
        'last_name' => 'Wonder',
        'role_id' => 1,
        'username' => 'alice',
        'email' => 'alice@example.com',
        'auth_key' => $security->generateRandomString(),
        'password_hash' => $security->generatePasswordHash($password),
        'status' => 1,
        'created_at' => $time,
        'updated_at' => $time,
      ],
      [
        'first_name' => 'Bob',
        'last_name' => 'Builder',
        'role_id' => 1,
        'username' => 'bob',
        'email' => 'bob@example.com',
        'auth_key' => $security->generateRandomString(),
        'password_hash' => $security->generatePasswordHash($password),
        'status' => 1,
        'created_at' => $time,
        'updated_at' => $time,
      ],
      [
        'first_name' => 'Charlie',
        'last_name' => 'Chaplin',
        'role_id' => 1,
        'username' => 'charlie',
        'email' => 'charlie@example.com',
        'auth_key' => $security->generateRandomString(),
        'password_hash' => $security->generatePasswordHash($password),
        'status' => 1,
        'created_at' => $time,
        'updated_at' => $time,
      ],
    ];

    foreach ($users as $user) {
      $this->insert('{{%user}}', $user);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->delete('{{%user}}', ['username' => ['alice', 'bob', 'charlie']]);
  }
}
