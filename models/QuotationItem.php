<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "quotation_item".
 *
 * @property int $id
 * @property int|null $quotation_id
 * @property int $product_id
 * @property string $product_name
 * @property string $sku
 * @property string $serial
 * @property string $unit
 * @property int $quantity
 * @property string $description
 * @property string $discount_type
 * @property float $full_price
 * @property float $discount
 * @property float $cost
 * @property float $price
 */
class QuotationItem extends \yii\db\ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'quotation_item';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [['quotation_id', 'product_id', 'quantity'], 'integer'],
      [['product_id', 'product_name', 'unit', 'discount_type'], 'required'],
      [['description'], 'string'],
      [['full_price', 'discount', 'cost', 'price'], 'default', 'value' => 0],
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
      'quotation_id' => 'Quotation ID',
      'product_id' => 'Product ID',
      'product_name' => 'Product Name',
      'sku' => 'SKU',
      'serial' => 'Serial',
      'unit' => 'Unit',
      'quantity' => 'Quantity',
      'description' => 'Description',
      'discount_type' => 'Discount Type',
      'discount' => 'Discount',
      'full_price' => 'Full Price',
      'cost' => 'Cost',
      'price' => 'Price',
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getQuotation()
  {
    return $this->hasOne(Quotation::class, ['id' => 'quotation_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getProduct()
  {
    return $this->hasOne(Product::class, ['id' => 'product_id']);
  }
}
