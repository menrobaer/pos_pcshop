<?php

namespace app\models\website;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "product_brand".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $image_url
 * @property int|null $status
 */
class ProductBrand extends ActiveRecord
{
     const STATUS_DELETED = 10;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        // Adjust this if your actual database table name differs (e.g., 'brand')
        return 'product_brand';
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
            // String length validations based on column lengths
            [['name'], 'string', 'max' => 50],
            [['image_url'], 'string', 'max' => 255],
            [['status'], 'integer'],
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
            'image_url' => 'Image Url',
            'status' => 'Status',
        ];
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