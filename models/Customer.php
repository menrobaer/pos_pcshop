<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "customer".
 *
 * @property int $id
 * @property string $name
 * @property string|null $phone
 * @property string|null $address
 * @property int $status
 * @property string $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 */
class Customer extends \yii\db\ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'customer';
  }

  /**
   * {@inheritdoc}
   */
  const STATUS_ACTIVE = 1,
    STATUS_INACTIVE = 0;

  public function init()
  {
    parent::init();
    $this->status = self::STATUS_ACTIVE;
  }

  public function rules()
  {
    return [
      [['name'], 'required'],
      [['status', 'created_by', 'updated_by'], 'integer'],
      [['created_at', 'updated_at'], 'safe'],
      [['name', 'phone'], 'string', 'max' => 50],
      [['address'], 'string', 'max' => 255],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'name' => 'Name',
      'phone' => 'Phone',
      'address' => 'Address',
      'status' => 'Status',
      'created_at' => 'Created At',
      'created_by' => 'Created By',
      'updated_at' => 'Updated At',
      'updated_by' => 'Updated By',
    ];
  }

  public function isUsed(){
    return Invoice::find()->where(['customer_id' => $this->id])->exists();
  }

  public function beforeSave($insert)
  {
    if (parent::beforeSave($insert)) {
      if ($this->isNewRecord) {
        $this->created_at = date('Y-m-d H:i:s');
        $this->created_by = Yii::$app->user->identity->id;
      } else {
        $this->updated_at = date('Y-m-d H:i:s');
        $this->updated_by = Yii::$app->user->identity->id;
      }
      return true;
    } else {
      return false;
    }
  }

  public function getStatusBadge()
  {
    if ($this->status == 1) {
      return '<span class="badge bg-info">Active</span>';
    } else {
      return '<span class="badge bg-danger">Inactive</span>';
    }
  }
}
