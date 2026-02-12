<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_role_permission".
 *
 * @property int $id
 * @property int|null $user_role_id
 * @property int|null $action_id
 */
class UserRolePermission extends \yii\db\ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'user_role_permission';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [[['user_role_id', 'action_id'], 'integer']];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'user_role_id' => 'User Role ID',
      'action_id' => 'Action ID',
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getRole()
  {
    return $this->hasOne(UserRole::class, ['id' => 'user_role_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getAction()
  {
    return $this->hasOne(UserRoleAction::class, ['id' => 'action_id']);
  }
}
