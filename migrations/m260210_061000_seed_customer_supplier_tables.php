<?php

use yii\db\Migration;

/**
 * Class m260210_061000_seed_customer_supplier_tables
 */
class m260210_061000_seed_customer_supplier_tables extends Migration
{
  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $faker = \Faker\Factory::create();

    // Seed Customers
    $customers = [];
    $prefixes = ['Bong', 'Mr.', 'Ms.', 'Mrs.', 'Oknha', 'Lok Chumteav'];
    for ($i = 0; $i < 20; $i++) {
      $prefix = $faker->randomElement($prefixes);
      $customers[] = [
        'name' => $prefix . ' ' . $faker->name,
        'phone' => $faker->phoneNumber,
        'address' => $faker->address,
        'status' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'created_by' => 1,
      ];
    }
    $this->batchInsert(
      '{{%customer}}',
      ['name', 'phone', 'address', 'status', 'created_at', 'created_by'],
      $customers,
    );

    // Seed Suppliers
    $suppliers = [];
    for ($i = 0; $i < 20; $i++) {
      $prefix = $faker->randomElement($prefixes);
      $suppliers[] = [
        'name' => $prefix . ' ' . $faker->company,
        'phone' => $faker->phoneNumber,
        'address' => $faker->address,
        'status' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'created_by' => 1,
      ];
    }
    $this->batchInsert(
      '{{%supplier}}',
      ['name', 'phone', 'address', 'status', 'created_at', 'created_by'],
      $suppliers,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->delete('{{%customer}}', ['created_by' => 1]);
    $this->delete('{{%supplier}}', ['created_by' => 1]);
  }
}
