<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%outlet}}`.
 */
class m260211_005130_create_outlet_table extends Migration
{
  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->createTable('{{%outlet}}', [
      'id' => $this->primaryKey(),
      'image' => $this->string(255),
      'name' => $this->string(50)->notNull(),
      'address' => $this->text(),
      'phone' => $this->string(50),
      'website' => $this->string(100),
      'email' => $this->string(100),
      'terms' => $this->text(),
      'status' => $this->smallInteger()->defaultValue(1),
      'created_at' => $this->dateTime(),
      'created_by' => $this->integer(),
      'updated_at' => $this->dateTime(),
      'updated_by' => $this->integer(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->dropTable('{{%outlet}}');
  }
}
