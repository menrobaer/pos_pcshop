<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%invoice_payment}}".
 *
 * @property int $id
 * @property int $invoice_id
 * @property int $payment_method_id
 * @property string $code
 * @property string $date
 * @property string|null $remark
 * @property float $amount
 * @property int $status
 * @property string $created_at
 * @property int|null $created_by
 */
class InvoicePayment extends ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'invoice_payment';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [
        ['invoice_id', 'payment_method_id', 'code', 'date', 'amount'],
        'required',
      ],
      [['invoice_id', 'payment_method_id', 'status', 'created_by'], 'integer'],
      [['date', 'created_at'], 'safe'],
      [['remark'], 'string'],
      [['amount'], 'number', 'min' => 0.01],
      [['code'], 'string', 'max' => 50],
      [
        ['invoice_id'],
        'exist',
        'skipOnError' => true,
        'targetClass' => Invoice::class,
        'targetAttribute' => ['invoice_id' => 'id'],
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
      'invoice_id' => 'Invoice',
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
  public function getInvoice()
  {
    return $this->hasOne(Invoice::class, ['id' => 'invoice_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getPaymentMethod()
  {
    return $this->hasOne(PaymentMethod::class, ['id' => 'payment_method_id']);
  }
}
