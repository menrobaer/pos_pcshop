<?php

namespace app\models\website;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "product_model".
 *
 * @property int $id
 * @property string|null $name
 * @property int|null $status
 * @property string|null $image_url
 */
class ProductModel extends ActiveRecord
{

    const STATUS_ACTIVE = 1,
    STATUS_INACTIVE = 0,
    STATUS_DELETED = 10;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        // Adjust this if your actual database table name differs (e.g., 'model')
        return 'product_model';
    }

    /**
     * {@inheritdoc}
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
            // Integer validation for status
            [['status'], 'integer'],

            // String length validations
            [['name'], 'string', 'max' => 50],
            [['image_url'], 'string', 'max' => 255],
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
            'status' => 'Status',
            'image_url' => 'Image Url',
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