<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "expense_item".
 *
 * @property int $id
 * @property int|null $expense_id
 * @property string $title
 * @property int $quantity
 * @property string $description
 * @property float $price
 */
class ExpenseItem extends \yii\db\ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'expense_item';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [['expense_id', 'quantity'], 'integer'],
      [['title', 'quantity'], 'required'],
      [['description'], 'string'],
      [['price'], 'number'],
      [['title'], 'string', 'max' => 100],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'expense_id' => 'Expense',
      'title' => 'Title',
      'quantity' => 'Quantity',
      'description' => 'Description',
      'price' => 'Price',
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getExpense()
  {
    return $this->hasOne(Expense::class, ['id' => 'expense_id']);
  }
}
