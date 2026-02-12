<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%payment_method}}".
 *
 * @property int $id
 * @property string $name
 */
class PaymentMethod extends ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'payment_method';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [[['name'], 'required'], [['name'], 'string', 'max' => 50]];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'name' => 'Name',
    ];
  }
}
