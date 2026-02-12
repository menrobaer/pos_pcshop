<?php

namespace app\models;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%inventory}}".
 *
 * @property int $id
 * @property int $product_id
 * @property string $type Transaction type (PO, Invoice, etc.)
 * @property int $transaction_id Reference to transaction
 * @property int $in Quantity in
 * @property int $out Quantity out
 * @property string $created_at
 * @property int $created_by
 *
 * @property Product $product
 */
class Inventory extends ActiveRecord
{
  /**
   * Transaction type constants
   */
  const TYPE_PURCHASE_ORDER = 'PO';
  const TYPE_INVOICE = 'INV';
  const TYPE_ADJUSTMENT = 'ADJ';
  const TYPE_RETURN = 'RET';
  const TYPE_OPENING = 'OPN';

  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return '{{%inventory}}';
  }

  /**
   * {@inheritdoc}
   */
  public function behaviors()
  {
    return [
      [
        'class' => TimestampBehavior::class,
        'value' => date('Y-m-d H:i:s'),
        'updatedAtAttribute' => false,
      ],
      [
        'class' => BlameableBehavior::class,
        'updatedByAttribute' => false,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [['product_id', 'type', 'transaction_id'], 'required'],
      [['product_id', 'transaction_id', 'in', 'out', 'created_by'], 'integer'],
      [['type'], 'string', 'max' => 10],
      [
        ['product_id'],
        'exist',
        'skipOnError' => true,
        'targetClass' => Product::class,
        'targetAttribute' => ['product_id' => 'id'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'product_id' => 'Product',
      'type' => 'Type',
      'transaction_id' => 'Transaction ID',
      'in' => 'Quantity In',
      'out' => 'Quantity Out',
      'created_at' => 'Created At',
      'created_by' => 'Created By',
    ];
  }

  /**
   * Get transaction types list
   * @return array
   */
  public static function getTypeList()
  {
    return [
      self::TYPE_PURCHASE_ORDER => 'Purchase Order',
      self::TYPE_INVOICE => 'Invoice',
      self::TYPE_ADJUSTMENT => 'Adjustment',
      self::TYPE_RETURN => 'Return',
      self::TYPE_OPENING => 'Opening Stock',
    ];
  }

  /**
   * Get type label
   * @return string
   */
  public function getTypeLabel()
  {
    return self::getTypeList()[$this->type] ?? $this->type;
  }

  /**
   * Calculate net movement (in - out)
   * @return int
   */
  public function getNetMovement()
  {
    return $this->in - $this->out;
  }

  /**
   * Get related Product
   * @return \yii\db\ActiveQuery
   */
  public function getProduct()
  {
    return $this->hasOne(Product::class, ['id' => 'product_id']);
  }
}
