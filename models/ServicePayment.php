<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%service_payment}}".
 *
 * @property int $id
 * @property int $service_id
 * @property int $payment_method_id
 * @property string $code
 * @property string $date
 * @property string|null $remark
 * @property float $amount
 * @property int $status
 * @property string $created_at
 * @property int|null $created_by
 */
class ServicePayment extends ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'service_payment';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [
        ['service_id', 'payment_method_id', 'code', 'date', 'amount'],
        'required',
      ],
      [['service_id', 'payment_method_id', 'status', 'created_by'], 'integer'],
      [['date', 'created_at'], 'safe'],
      [['remark'], 'string'],
      [['amount'], 'number', 'min' => 0.01],
      [['code'], 'string', 'max' => 50],
      [
        ['service_id'],
        'exist',
        'skipOnError' => true,
        'targetClass' => Service::class,
        'targetAttribute' => ['service_id' => 'id'],
      ],
      [
        ['payment_method_id'],
        'exist',
        'skipOnError' => true,
        'targetClass' => PaymentMethod::class,
        'targetAttribute' => ['payment_method_id' => 'id'],
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
      'service_id' => 'Service',
      'payment_method_id' => 'Payment Method',
      'code' => 'Code',
      'date' => 'Payment Date',
      'remark' => 'Remark',
      'amount' => 'Amount',
      'status' => 'Status',
      'created_at' => 'Created At',
      'created_by' => 'Created By',
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getService()
  {
    return $this->hasOne(Service::class, ['id' => 'service_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getPaymentMethod()
  {
    return $this->hasOne(PaymentMethod::class, ['id' => 'payment_method_id']);
  }
}
