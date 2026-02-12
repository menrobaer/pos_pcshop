<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "outlet".
 *
 * @property int $id
 * @property string|null $image
 * @property string $name
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $website
 * @property string|null $email
 * @property string|null $terms
 * @property int|null $status
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 */
class Outlet extends \yii\db\ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'outlet';
  }

  public $imageFile;

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [['name'], 'required'],
      [['address', 'terms'], 'string'],
      [['status', 'created_by', 'updated_by'], 'integer'],
      [['created_at', 'updated_at'], 'safe'],
      [['image'], 'string', 'max' => 255],
      [['name', 'phone'], 'string', 'max' => 50],
      [['website', 'email'], 'string', 'max' => 100],
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
      'image' => 'Logo',
      'imageFile' => 'Logo Image',
      'name' => 'Name',
      'address' => 'Address',
      'phone' => 'Phone',
      'website' => 'Website',
      'email' => 'Email',
      'terms' => 'Terms & Conditions',
      'status' => 'Status',
      'created_at' => 'Created At',
      'created_by' => 'Created By',
      'updated_at' => 'Updated At',
      'updated_by' => 'Updated By',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function beforeSave($insert)
  {
    if (parent::beforeSave($insert)) {
      if ($this->isNewRecord) {
        $this->created_at = date('Y-m-d H:i:s');
        $this->created_by = Yii::$app->user->id ?? null;
      } else {
        $this->updated_at = date('Y-m-d H:i:s');
        $this->updated_by = Yii::$app->user->id ?? null;
      }
      return true;
    }
    return false;
  }

  public function getImagePath()
  {
    if (!$this->image || !file_exists($this->image)) {
      return null;
    }
    return Yii::getAlias('@web') . '/' . $this->image;
  }

  public function uploadImage()
  {
    if ($this->validate(['imageFile']) && $this->imageFile) {
      $filePath = 'uploads/outlets';
      $directory = Yii::getAlias("@webroot/{$filePath}");
      if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
      }
      $randomString = Yii::$app->security->generateRandomString(16);
      $fileName =
        $this->imageFile->baseName .
        '-' .
        $randomString .
        '.' .
        $this->imageFile->extension;
      $path = $directory . '/' . $fileName;

      if ($this->imageFile->saveAs($path)) {
        return $filePath . '/' . $fileName;
      }
    }
    return false;
  }
}
