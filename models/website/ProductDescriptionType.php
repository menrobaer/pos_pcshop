<?php

namespace app\models\website;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "product_description_type".
 *
 * @property int $id
 * @property string $name
 * @property string|null $display_name
 * @property int|null $status
 * @property int|null $sort
 */
class ProductDescriptionType extends ActiveRecord
{

    const STATUS_DELETED = 10;
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        // Adjust this if your actual database table name differs
        return 'product_description_type';
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
            // Required fields (Not Null = true)
            [['name'], 'required'],

            // Integer validations
            [['status', 'sort'], 'integer'],

            // Default value configuration
            [['status'], 'default', 'value' => 1],

            // String length validations
            [['name'], 'string', 'max' => 50],
            [['display_name'], 'string', 'max' => 20],
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
            'display_name' => 'Display Name',
            'status' => 'Status',
            'sort' => 'Sort',
        ];
    }

    public function isUsed()
    {
        return false;
    }

    public function getStatusBadge()
    {
        if ($this->status == self::STATUS_ACTIVE) {
        return '<span class="badge bg-info">Active</span>';
        } else {
        return '<span class="badge bg-danger">Inactive</span>';
        }
    }
}