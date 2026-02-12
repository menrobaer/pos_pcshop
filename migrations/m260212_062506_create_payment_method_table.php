<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%payment_method}}`.
 */
class m260212_062506_create_payment_method_table extends Migration
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
      '{{%payment_method}}',
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
    $this->dropTable('{{%payment_method}}');
  }
}
