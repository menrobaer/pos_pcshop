<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "activity_log".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $controller
 * @property string|null $action
 * @property string|null $method
 * @property string|null $params
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string $created_at
 */
class ActivityLog extends \yii\db\ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'activity_log';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [['user_id'], 'integer'],
      [['params', 'user_agent'], 'string'],
      [['created_at'], 'required'],
      [['created_at'], 'safe'],
      [['controller', 'action'], 'string', 'max' => 100],
      [['method'], 'string', 'max' => 10],
      [['ip_address'], 'string', 'max' => 45],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'user_id' => 'User ID',
      'controller' => 'Controller',
      'action' => 'Action',
      'method' => 'Method',
      'params' => 'Params',
      'ip_address' => 'Ip Address',
      'user_agent' => 'User Agent',
      'created_at' => 'Created At',
    ];
  }

  public function formatTimestamp()
  {
    return Yii::$app->utils::dateTime($this->created_at);
  }

  public function getUser()
  {
    return $this->hasOne(User::class, ['id' => 'user_id']);
  }
}
