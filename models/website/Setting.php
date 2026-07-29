<?php

namespace app\models\website;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "setting".
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $key
 * @property string|null $value
 */
class Setting extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        // Adjust this if your actual database table name differs (e.g., 'source')
        return 'setting';
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
            [['title','key', 'value'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'key' => 'Key',
            'value' => 'Value',
        ];
    }

    public function isUsed()
    {
        return false;
    }
}