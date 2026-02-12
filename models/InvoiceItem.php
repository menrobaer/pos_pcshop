<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "invoice_item".
 *
 * @property int $id
 * @property int|null $invoice_id
 * @property int $product_id
 * @property string $product_name
 * @property string $sku
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
class InvoiceItem extends \yii\db\ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'invoice_item';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [['invoice_id', 'product_id', 'quantity'], 'integer'],
      [
        ['product_id', 'product_name', 'sku', 'unit', 'discount_type'],
        'required',
      ],
      [['description'], 'string'],
      [['full_price', 'discount', 'cost', 'price'], 'number'],
      [['product_name'], 'string', 'max' => 100],
      [['sku', 'serial'], 'string', 'max' => 50],
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
      'invoice_id' => 'Invoice',
      'product_id' => 'Product',
      'product_name' => 'Product Name',
      'sku' => 'Sku',
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
