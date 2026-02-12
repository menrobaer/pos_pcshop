<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "product_category".
 *
 * @property int $id
 * @property string $name
 * @property int $status
 * @property string $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 */
class ProductCategory extends ActiveRecord
{
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
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'product_category';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [['name', 'status'], 'required'],
      [['status', 'created_by', 'updated_by'], 'integer'],
      [['created_at', 'updated_at'], 'safe'],
      [['name'], 'string', 'max' => 50],
      ['status', 'default', 'value' => 1],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'name' => 'Category Name',
      'status' => 'Status',
      'created_at' => 'Created At',
      'created_by' => 'Created By',
      'updated_at' => 'Updated At',
      'updated_by' => 'Updated By',
    ];
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

  public function isUsed()
  {
    return Product::find()->where(['category_id' => $this->id])->exists();
  }
  /**
   * Gets query for [[User]] (Created By).
   */
  public function getCreator()
  {
    return $this->hasOne(User::class, ['id' => 'created_by']);
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
