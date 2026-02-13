<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property int $role_id
 * @property string|null $image
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string|null $password_reset_token
 * @property string $email
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 * @property string|null $verification_token
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{
  const STATUS_ACTIVE = 1;
  const STATUS_INACTIVE = 0;

  public $new_password;

  public function init()
  {
    parent::init();
    $this->status = self::STATUS_ACTIVE;
  }
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'user';
  }

  /**
   * {@inheritdoc}
   */

  public $imageFile;
  public function rules()
  {
    return [
      [['first_name', 'last_name', 'role_id', 'username', 'email'], 'required'],
      [['new_password'], 'required', 'on' => 'create'],
      [['role_id', 'status', 'created_at', 'updated_at'], 'integer'],
      [
        [
          'first_name',
          'last_name',
          'image',
          'username',
          'password_hash',
          'password_reset_token',
          'email',
          'verification_token',
          'new_password',
        ],
        'string',
        'max' => 255,
      ],
      [['auth_key'], 'string', 'max' => 32],
      [['username'], 'unique'],
      [['email'], 'unique'],
      [['password_reset_token'], 'unique'],
      [
        ['imageFile'],
        'image',
        'skipOnEmpty' => true,
        'extensions' => 'jpg, jpeg, gif, png, webp',
        'maxSize' => 1024 * 1024 * 2,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'first_name' => 'First Name',
      'last_name' => 'Last Name',
      'role_id' => 'Role',
      'image' => 'Image',
      'username' => 'Username',
      'auth_key' => 'Auth Key',
      'password_hash' => 'Password Hash',
      'password_reset_token' => 'Password Reset Token',
      'email' => 'Email',
      'new_password' => 'Password',
      'status' => 'Status',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
      'verification_token' => 'Verification Token',
    ];
  }

  public function getName()
  {
    return $this->first_name . ' ' . $this->last_name;
  }

  /**
   * {@inheritdoc}
   */
  public static function findIdentity($id)
  {
    return static::findOne($id);
  }

  /**
   * {@inheritdoc}
   */
  public static function findIdentityByAccessToken($token, $type = null)
  {
    return static::findOne(['auth_key' => $token]);
  }

  /**
   * Finds user by username
   *
   * @param string $username
   * @return static|null
   */
  public static function findByUsername($username)
  {
    return static::findOne(['username' => $username, 'status' => 1]);
  }

  /**
   * {@inheritdoc}
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthKey()
  {
    return $this->auth_key;
  }

  /**
   * {@inheritdoc}
   */
  public function validateAuthKey($authKey)
  {
    return $this->getAuthKey() === $authKey;
  }

  /**
   * Validates password
   *
   * @param string $password password to validate
   * @return bool if password provided is valid for current user
   */
  public function validatePassword($password)
  {
    return Yii::$app->security->validatePassword(
      $password,
      $this->password_hash,
    );
  }

  /**
   * @return string
   */
  public function getStatusBadge()
  {
    if ($this->status == self::STATUS_ACTIVE) {
      return '<span class="badge bg-info">Active</span>';
    } else {
      return '<span class="badge bg-danger">Inactive</span>';
    }
  }

  public function getImagePath()
  {
    $placeholder = Yii::getAlias('@web/images/users/user-dummy-img.jpg');
    if (
      !$this->image ||
      !file_exists(Yii::getAlias('@webroot/' . $this->image))
    ) {
      return $placeholder;
    }
    return Yii::getAlias('@web') . '/' . $this->image;
  }

  public function uploadImage()
  {
    if ($this->validate() && $this->imageFile) {
      $filePath = 'uploads/users';
      $directory = Yii::getAlias("@webroot/{$filePath}");
      if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
      }
      $randomString = Yii::$app->security->generateRandomString(16);
      $path =
        $directory .
        '/' .
        $this->imageFile->baseName .
        '-' .
        $randomString .
        '.' .
        $this->imageFile->extension;
      if ($this->imageFile->saveAs($path)) {
        return $filePath .
          '/' .
          $this->imageFile->baseName .
          '-' .
          $randomString .
          '.' .
          $this->imageFile->extension;
      }
    }
    return false;
  }


  public static function getUserPermission($controller)
  {
    $permission = UserRolePermission::find()
      ->select('user_role_action.action')
      ->innerJoin('user_role_action', 'user_role_action.id = user_role_permission.action_id')
      ->innerJoin('user_role', 'user_role.id = user_role_permission.user_role_id')
      ->where(['user_role.id' => Yii::$app->user->identity->role_id])
      ->andWhere(['user_role_action.controller' => $controller])
      ->asArray()
      ->all();
    $array = ['get-product', 'product-search', 'ajax-request', 'clear-filter', 'export-csv'];
    foreach ($permission as $row) {
      $extra_actions =  explode(",", $row["action"]);
      foreach ($extra_actions as $ex) {
        array_push($array, $ex);
      }
    }

    return $array;
  }
}
