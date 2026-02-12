<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_role_action".
 *
 * @property int $id
 * @property int|null $group_id
 * @property string $name
 * @property string $controller
 * @property string $action
 * @property int $status
 */
class UserRoleAction extends \yii\db\ActiveRecord
{
  const STATUS_ACTIVE = 1;
  const STATUS_INACTIVE = 0;

  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'user_role_action';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [['group_id', 'status'], 'integer'],
      [['name', 'controller', 'action'], 'required'],
      [['action'], 'string'],
      [['name', 'controller'], 'string', 'max' => 50],
      [['status'], 'default', 'value' => self::STATUS_ACTIVE],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'group_id' => 'Group ID',
      'name' => 'Action Name',
      'controller' => 'Controller',
      'action' => 'Action',
      'status' => 'Status',
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getGroup()
  {
    return $this->hasOne(UserRoleGroup::class, ['id' => 'group_id']);
  }
}
