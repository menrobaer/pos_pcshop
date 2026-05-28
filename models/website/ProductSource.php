<?php

namespace app\models\website;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "product_source".
 *
 * @property int $id
 * @property string|null $name
 * @property int|null $status
 * @property string|null $color
 * @property string|null $background_color
 */
class ProductSource extends ActiveRecord
{
     const STATUS_DELETED = 10;
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        // Adjust this if your actual database table name differs (e.g., 'source')
        return 'product_source';
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
            // String length validations based on column lengths
            [['name', 'color', 'background_color'], 'string', 'max' => 50],
            [['status'], 'integer'],
            [['status'], 'default', 'value' => 1],
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
            'color' => 'Color',
            'background_color' => 'Background Color',
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