<?php

namespace app\models\website;

use Yii;

/**
 * This is the model class for table "navigation".
 *
 * @property int $id
 * @property string|null $name
 */
class Navigation extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'navigation';
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
            [['name', 'slug'], 'required'],
            [['sort'], 'integer'],
            [['name', 'slug'], 'string', 'max' => 50],
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
            'sort' => 'Sort',
        ];
    }

    public function getItem()
    {
        return $this->hasMany(NavigationItem::class, ['nav_id' => 'id'])->orderBy(['navigation_item.sort' => SORT_ASC]);
    }
}
