<?php

namespace app\models\website;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "product_category".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $slug
 * @property int|null $status
 * @property string|null $image_url
 * @property int|null $sort
 */
class ProductCategory extends ActiveRecord
{
    const STATUS_DELETED = 10;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        // Adjust this if your actual database table name differs (e.g., 'category')
        return 'product_category';
    }

    /**
     * Uses the `website` DB connection component.
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->get('website');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
          [['name'], 'required'],
            // Integer validations for status and sort
            [['status', 'sort'], 'integer'],
            
            // Default value configuration
            [['sort'], 'default', 'value' => 1],
            
            // String length validations
            [['slug'], 'string', 'max' => 50],
            [['name', 'image_url'], 'string', 'max' => 255],
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
            'slug' => 'Slug',
            'status' => 'Status',
            'image_url' => 'Image Url',
            'sort' => 'Sort',
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
    return false;
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