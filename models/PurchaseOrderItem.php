<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "purchase_order_item".
 *
 * @property int $id
 * @property int|null $purchase_order_id
 * @property int|null $product_id
 * @property string|null $serial
 * @property int|null $quantity
 * @property float|null $full_price
 * @property float|null $price
 * @property string|null $discount_type
 * @property float|null $discount
 *
 * @property Product $product
 * @property PurchaseOrder $purchaseOrder
 */
class PurchaseOrderItem extends ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'purchase_order_item';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [['purchase_order_id', 'product_id', 'quantity'], 'integer'],
      [['full_price', 'price', 'discount'], 'number'],
      [['quantity'], 'default', 'value' => 1],
      [['full_price', 'price', 'discount'], 'default', 'value' => 0.00],
      [['serial'], 'string', 'max' => 1000],
      [['discount_type'], 'string', 'max' => 10],
      [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
      [['purchase_order_id'], 'exist', 'skipOnError' => true, 'targetClass' => PurchaseOrder::class, 'targetAttribute' => ['purchase_order_id' => 'id']],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'purchase_order_id' => 'Purchase Order',
      'product_id' => 'Product',
      'serial' => 'Serial Number',
      'quantity' => 'Qty',
      'full_price' => 'Original Price',
      'price' => 'Net Price (Cost)',
      'discount_type' => 'Discount Type',
      'discount' => 'Discount Value',
    ];
  }

  /**
   * Gets query for [[Product]].
   */
  public function getProduct()
  {
    return $this->hasOne(Product::class, ['id' => 'product_id']);
  }

  /**
   * Gets query for [[PurchaseOrder]].
   */
  public function getPurchaseOrder()
  {
    return $this->hasOne(PurchaseOrder::class, ['id' => 'purchase_order_id']);
  }

  /**
   * Custom Helper to calculate the total cost for this line item
   */
  public function getLineTotal()
  {
    return $this->price * $this->quantity;
  }
}
