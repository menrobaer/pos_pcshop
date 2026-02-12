<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_role_group".
 *
 * @property int $id
 * @property string $name
 */
class UserRoleGroup extends \yii\db\ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'user_role_group';
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
      'name' => 'Group Name',
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getActions()
  {
    return $this->hasMany(UserRoleAction::class, ['group_id' => 'id']);
  }
}
