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
 * @property string|null $terms_service
 * @property int|null $status
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 * @property int $is_website
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

  public $imageFile, $signatureFile;

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [['name'], 'required'],
      [['address', 'terms', 'terms_service'], 'string'],
      [['status', 'created_by', 'updated_by', 'is_website'], 'integer'],
      [['created_at', 'updated_at'], 'safe'],
      [['image', 'signature'], 'string', 'max' => 255],
      [['name', 'phone'], 'string', 'max' => 50],
      [['website', 'email'], 'string', 'max' => 100],
      [
        ['imageFile'],
        'image',
        'skipOnEmpty' => true,
        'extensions' => 'jpg, jpeg, gif, png, webp',
        'maxSize' => 1024 * 1024 * 2,
      ],
      [
        ['signatureFile'],
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
      'signature' => 'Signature',
      'imageFile' => 'Logo Image',
      'signatureFile' => 'Signature Image',
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

  public function getSignaturePath()
  {
    if (!$this->signature || !file_exists($this->signature)) {
      return null;
    }
    return Yii::getAlias('@web') . '/' . $this->signature;
  }

  public function uploadSignature()
  {
    if ($this->validate(['signatureFile']) && $this->signatureFile) {
      $filePath = 'uploads/outlets/signatures';
      $directory = Yii::getAlias("@webroot/{$filePath}");
      if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
      }
      $randomString = Yii::$app->security->generateRandomString(16);
      $fileName =
        $this->signatureFile->baseName .
        '-' .
        $randomString .
        '.' .
        $this->signatureFile->extension;
      $path = $directory . '/' . $fileName;

      if ($this->signatureFile->saveAs($path)) {
        return $filePath . '/' . $fileName;
      }
    }
    return false;
  }
}
