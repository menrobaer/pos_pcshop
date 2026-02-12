<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%invoice_item}}`.
 */
class m260210_052248_create_invoice_item_table extends Migration
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
      '{{%invoice_item}}',
      [
        'id' => $this->primaryKey(),
        'invoice_id' => $this->integer(),
        'product_id' => $this->integer()->notNull(),
        'product_name' => $this->string(100)->notNull(),
        'sku' => $this->string(50)->notNull(),
        'serial' => $this->string(50)->notNull(),
        'unit' => $this->string(20)->notNull(),
        'quantity' => $this->integer()->notNull()->defaultValue(1),
        'description' => $this->text()->notNull(),
        'discount_type' => $this->string(10)->notNull(),
        'discount' => $this->decimal(10, 2)->notNull()->defaultValue(0),
        'full_price' => $this->decimal(10, 2)->notNull()->defaultValue(0),
        'cost' => $this->decimal(10, 2)->notNull()->defaultValue(0),
        'price' => $this->decimal(10, 2)->notNull()->defaultValue(0),
      ],
      $tableOptions,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->dropTable('{{%invoice_item}}');
  }
}
