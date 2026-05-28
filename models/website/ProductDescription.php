<?php

namespace app\models\website;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "product_description".
 *
 * @property int $id
 * @property int $variation_id
 * @property int $type_id
 * @property string $description
 * @property int|null $status
 */
class ProductDescription extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        // Adjust this if your actual database table name differs
        return 'product_description';
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
            [['variation_id', 'type_id', 'description'], 'required'],

            // Integer validations
            [['variation_id', 'type_id', 'status'], 'integer'],

            // Default value configuration
            [['status'], 'default', 'value' => 1],

            // String length validation
            [['description'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'variation_id' => 'Variation ID',
            'type_id' => 'Type ID',
            'description' => 'Description',
            'status' => 'Status',
        ];
    }
}