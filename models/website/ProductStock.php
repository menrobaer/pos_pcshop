<?php

namespace app\models\website;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "product_stock".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $color
 * @property string|null $background_color
 */
class ProductStock extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        // Adjust this if your actual database table name differs (e.g., 'source')
        return 'product_stock';
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
}