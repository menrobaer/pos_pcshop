<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%purchase_order_item}}`.
 */
class m260210_053400_create_purchase_order_item_table extends Migration
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
      '{{%purchase_order_item}}',
      [
        'id' => $this->primaryKey(),
        'purchase_order_id' => $this->integer(),
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
    $this->dropTable('{{%purchase_order_item}}');
  }
}
