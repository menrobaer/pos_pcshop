<?php

namespace app\models\website;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "warranty".
 *
 * @property int $id
 * @property string|null $name
 */
class Warranty extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        // Adjust this if your actual database table name differs
        return 'warranty';
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
            [['name'], 'string', 'max' => 50],
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
        ];
    }
}