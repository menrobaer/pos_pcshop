<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "purchase_order".
 *
 * @property int $id
 * @property string $code
 * @property string $serial_code
 * @property int $supplier_id
 * @property string $date
 * @property string $due_date
 * @property string $remark
 * @property float $delivery_fee
 * @property float $extra_charge
 * @property float $discount_amount
 * @property float $cost_total
 * @property float $sub_total
 * @property float $grand_total
 * @property float $paid_amount
 * @property float $balance_amount
 * @property int $status
 * @property string $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 */
class PurchaseOrder extends \yii\db\ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'purchase_order';
  }

  const STATUS_ACTIVE = 1,
    STATUS_PAID = 2,
    STATUS_PROCESS = 3,
    STATUS_CANCELLED = 0;

  /**
   * {@inheritdoc}
   */
  public function behaviors()
  {
    return [
      [
        'class' => TimestampBehavior::class,
        'value' => new Expression('NOW()'),
      ],
      [
        'class' => BlameableBehavior::class,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [['code', 'serial_code', 'supplier_id', 'date', 'due_date'], 'required'],
      [['supplier_id', 'status', 'created_by', 'updated_by'], 'integer'],
      [['date', 'due_date', 'created_at', 'updated_at'], 'safe'],
      [['remark'], 'string'],
      [
        [
          'delivery_fee',
          'extra_charge',
          'discount_amount',
          'cost_total',
          'sub_total',
          'grand_total',
          'paid_amount',
          'balance_amount',
        ],
        'number',
      ],
      [['code', 'serial_code'], 'string', 'max' => 50],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'quotation_id' => 'Quotation',
      'code' => 'Code',
      'serial_code' => 'Serial Code',
      'supplier_id' => 'Customer',
      'date' => 'Date',
      'due_date' => 'Due Date',
      'remark' => 'Remark',
      'delivery_fee' => 'Delivery Fee',
      'extra_charge' => 'Extra Charge',
      'discount_amount' => 'Discount Amount',
      'cost_total' => 'Cost Total',
      'sub_total' => 'Sub Total',
      'grand_total' => 'Grand Total',
      'paid_amount' => 'Paid Amount',
      'balance_amount' => 'Balance Amount',
      'status' => 'Status',
      'created_at' => 'Created At',
      'created_by' => 'Created By',
      'updated_at' => 'Updated At',
      'updated_by' => 'Updated By',
    ];
  }

  public static function getStatusList()
  {
    return [
      self::STATUS_ACTIVE => 'Active',
      self::STATUS_PAID => 'Paid',
      self::STATUS_PROCESS => 'Process',
      self::STATUS_CANCELLED => 'Cancelled',
    ];
  }

  public function getStatusLabel()
  {
    return self::getStatusList()[$this->status] ?? 'Unknown';
  }

  public function getStatusBadge()
  {
    if (
      $this->due_date < date('Y-m-d') &&
      ($this->status == self::STATUS_ACTIVE ||
        $this->status == self::STATUS_PROCESS)
    ) {
      return '<span class="badge bg-danger-subtle text-danger">Expired</span>';
    }
    switch ($this->status) {
      case self::STATUS_ACTIVE:
        return '<span class="badge bg-success-subtle text-success">Active</span>';
      case self::STATUS_PAID:
        return '<span class="badge bg-primary-subtle text-primary">Paid</span>';
      case self::STATUS_PROCESS:
        return '<span class="badge bg-warning-subtle text-warning">Process</span>';
      case self::STATUS_CANCELLED:
        return '<span class="badge bg-secondary-subtle text-secondary">Cancelled</span>';
      default:
        return '<span class="badge bg-secondary-subtle text-secondary">Unknown</span>';
    }
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getSupplier()
  {
    return $this->hasOne(Supplier::class, ['id' => 'supplier_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getItems()
  {
    return $this->hasMany(PurchaseOrderItem::class, [
      'purchase_order_id' => 'id',
    ]);
  }
}
