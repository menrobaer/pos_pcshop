<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "service_item".
 *
 * @property int $id
 * @property int|null $service_id
 * @property string $name
 * @property string $serial
 * @property string $unit
 * @property int $quantity
 * @property string $description
 * @property string $discount_type
 * @property float $discount
 * @property float $full_price
 * @property float $cost
 * @property float $price
 */
class ServiceItem extends \yii\db\ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'service_item';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [['service_id', 'quantity'], 'integer'],
      [
        ['name', 'discount_type'],
        'required',
      ],
      [['description'], 'string'],
      [['full_price', 'discount', 'cost', 'price'], 'number'],
      [['name'], 'string', 'max' => 100],
      [['serial'], 'string', 'max' => 50],
      [['unit'], 'string', 'max' => 20],
      [['discount_type'], 'string', 'max' => 10],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'service_id' => 'Service',
      'name' => 'Service Name',
      'serial' => 'Serial',
      'unit' => 'Unit',
      'quantity' => 'Quantity',
      'description' => 'Description',
      'discount_type' => 'Discount Type',
      'discount' => 'Discount',
      'cost' => 'Cost',
      'price' => 'Price',
    ];
  }
}
