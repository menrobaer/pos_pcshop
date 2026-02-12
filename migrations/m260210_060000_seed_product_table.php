<?php

use yii\db\Migration;

/**
 * Class m260210_060000_seed_product_table
 */
class m260210_060000_seed_product_table extends Migration
{
  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $faker = \Faker\Factory::create();

    // Ensure some brands and categories exist
    $brandNames = [
      'Logitech',
      'Razer',
      'Corsair',
      'Samsung',
      'Crucial',
      'Nvidia',
      'Intel',
      'AMD',
      'Asus',
      'MSI',
      'SteelSeries',
      'HyperX',
    ];
    $brandIds = \app\models\ProductBrand::find()->select('id')->column();
    if (empty($brandIds)) {
      foreach ($brandNames as $name) {
        $this->insert('{{%product_brand}}', [
          'name' => $name,
          'status' => 1,
          'created_at' => date('Y-m-d H:i:s'),
          'created_by' => 1,
        ]);
      }
      $brandIds = \app\models\ProductBrand::find()->select('id')->column();
    }

    $categoryNames = [
      'Peripherals',
      'Components',
      'Monitors',
      'Accessories',
      'Storage',
      'Audio',
    ];
    $categoryIds = \app\models\ProductCategory::find()->select('id')->column();
    if (empty($categoryIds)) {
      foreach ($categoryNames as $name) {
        $this->insert('{{%product_category}}', [
          'name' => $name,
          'status' => 1,
          'created_at' => date('Y-m-d H:i:s'),
          'created_by' => 1,
        ]);
      }
      $categoryIds = \app\models\ProductCategory::find()
        ->select('id')
        ->column();
    }

    $productNames = [
      'Gaming Mouse',
      'Mechanical Keyboard',
      '27-inch 4K Monitor',
      'Wireless Headset',
      'RGB Mousepad',
      '1TB NVMe SSD',
      '16GB DDR4 RAM (2x8GB)',
      'GeForce RTX 4070 Graphics Card',
      'Intel Core i7-13700K CPU',
      '850W 80+ Gold Power Supply',
      'Mid-Tower ATX Case',
      '240mm Liquid CPU Cooler',
      '1080p Webcam with Microphone',
      'USB-C Hub 7-in-1',
      '2TB External Hard Drive',
      'Ergonomic Laptop Stand',
      'Gaming Chair',
      '2.1 Channel Speaker System',
      'Wi-Fi 6 Mesh Router',
      '10ft Braided Ethernet Cable',
    ];

    $products = [];
    for ($i = 0; $i < 50; $i++) {
      $cost = $faker->randomFloat(2, 10, 1000);
      $name =
        $i < count($productNames)
          ? $productNames[$i]
          : $faker->unique()->sentence(3);
      $products[] = [
        'name' => $name,
        'image' => null,
        'sku' => $faker->unique()->regexify('[A-Z0-9]{5}'),
        'serial' => \Yii::$app->security->generateRandomString(12),
        'brand_id' => $faker->randomElement($brandIds),
        'category_id' => $faker->randomElement($categoryIds),
        'description' => $faker->realText(),
        'cost' => $cost,
        'price' => $cost * $faker->randomFloat(2, 1.2, 1.8),
        'status' => 1, // Active
        'available' => $faker->numberBetween(0, 100),
        'created_at' => date('Y-m-d H:i:s'),
        'created_by' => 1, // Assuming user with ID 1 is an admin/system
        'updated_at' => null,
        'updated_by' => null,
      ];
    }

    $this->batchInsert(
      '{{%product}}',
      [
        'name',
        'image',
        'sku',
        'serial',
        'brand_id',
        'category_id',
        'description',
        'cost',
        'price',
        'status',
        'available',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
      ],
      $products,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    // This will delete all products, brands, and categories created by user 1.
    // Be careful if user 1 has created other records.
    $this->delete('{{%product}}', ['created_by' => 1]);
    $this->delete('{{%product_brand}}', ['created_by' => 1]);
    $this->delete('{{%product_category}}', ['created_by' => 1]);
  }
}
