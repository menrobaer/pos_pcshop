<?php

namespace app\models\website;

use Yii;

/**
 * This is the model class for table "navigation_item_data".
 *
 * @property int $id
 * @property int|null $nav_item_id
 * @property string|null $name
 * @property int|null $category_id
 * @property int|null $brand_id
 * @property int|null $model_id
 */
class NavigationItemData extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'navigation_item_data';
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
            [['nav_item_id', 'category_id', 'brand_id', 'model_id'], 'integer'],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nav_item_id' => 'Nav Item ID',
            'category_id' => 'Category ID',
            'brand_id' => 'Brand ID',
            'model_id' => 'Model ID',
        ];
    }
}
