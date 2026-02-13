<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%product}}`.
 */
class m260210_051345_create_product_table extends Migration
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
      '{{%product}}',
      [
        'id' => $this->primaryKey(),
        'image' => $this->string(),
        'name' => $this->string(100)->notNull(),
        'sku' => $this->string(50),
        'brand_id' => $this->integer()->notNull(),
        'category_id' => $this->integer()->notNull(),
        'description' => $this->text(),
        'cost' => $this->decimal(10, 2)->notNull()->defaultValue(0),
        'price' => $this->decimal(10, 2)->notNull()->defaultValue(0),
        'status' => $this->smallInteger()->notNull()->defaultValue(1),
        'available' => $this->integer()->notNull()->defaultValue(0),
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
    $this->dropTable('{{%product}}');
  }
}
