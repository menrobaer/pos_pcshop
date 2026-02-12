<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "quotation".
 *
 * @property int $id
 * @property string $code
 * @property string $serial_code
 * @property int $customer_id
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
class Quotation extends \yii\db\ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'quotation';
  }

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
      BlameableBehavior::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [['code', 'serial_code', 'customer_id', 'date', 'due_date'], 'required'],
      [['customer_id', 'status', 'created_by', 'updated_by'], 'integer'],
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
      [
        'due_date',
        'compare',
        'compareAttribute' => 'date',
        'operator' => '>=',
        'type' => 'date',
        'message' =>
          '{attribute} must be greater than or equal to "{compareValueOrAttribute}".',
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
      'code' => 'Code',
      'serial_code' => 'Serial Code',
      'customer_id' => 'Customer',
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

  const STATUS_EXPIRED = 0;
  const STATUS_ACTIVE = 1;
  const STATUS_ACCEPTED = 2;
  const STATUS_CANCELLED = 3;

  public static function getStatusList()
  {
    return [
      self::STATUS_ACTIVE => 'Active',
      self::STATUS_EXPIRED => 'Expired',
      self::STATUS_ACCEPTED => 'Accepted',
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
      $this->status == self::STATUS_ACTIVE
    ) {
      return '<span class="badge bg-danger-subtle text-danger">Expired</span>';
    }
    switch ($this->status) {
      case self::STATUS_ACTIVE:
        return '<span class="badge bg-success-subtle text-success">Active</span>';
      case self::STATUS_EXPIRED:
        return '<span class="badge bg-danger-subtle text-danger">Expired</span>';
      case self::STATUS_ACCEPTED:
        return '<span class="badge bg-primary-subtle text-primary">Accepted</span>';
      case self::STATUS_CANCELLED:
        return '<span class="badge bg-secondary-subtle text-secondary">Cancelled</span>';
      default:
        return '<span class="badge bg-secondary-subtle text-secondary">Unknown</span>';
    }
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getCustomer()
  {
    return $this->hasOne(Customer::class, ['id' => 'customer_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getItems()
  {
    return $this->hasMany(QuotationItem::class, ['quotation_id' => 'id']);
  }
}
