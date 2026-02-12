<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_role".
 *
 * @property int $id
 * @property string $name
 */
class UserRole extends \yii\db\ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'user_role';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [[['name'], 'required'], [['name'], 'string', 'max' => 50]];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'name' => 'Role Name',
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getPermissions()
  {
    return $this->hasMany(UserRolePermission::class, ['user_role_id' => 'id']);
  }
}
