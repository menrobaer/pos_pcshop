<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "product".
 *
 * @property int $id
 * @property string|null $image
 * @property string $name
 * @property string $sku
 * @property int $brand_id
 * @property int $category_id
 * @property int $model_id
 * @property string $description
 * @property float $cost
 * @property float $price
 * @property int $status
 * @property int $available
 * @property string $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 */
class Product extends \yii\db\ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'product';
  }

  /**
   * {@inheritdoc}
   */
  const STATUS_ACTIVE = 1,
    STATUS_INACTIVE = 0,
    STATUS_DELETED = 10;
  public $imageFile;

  public function init()
  {
    parent::init();
    $this->status = self::STATUS_ACTIVE;
  }

  public function rules()
  {
    return [
      [['name', 'brand_id', 'category_id', 'created_at', 'cost', 'price'], 'required'],
      [
        [
          'brand_id',
          'category_id',
          'model_id',
          'status',
          'created_by',
          'updated_by',
          'available',
        ],
        'integer',
      ],
      [['description'], 'string'],
      [['cost', 'price'], 'number', 'min' => 0.01],
      [['created_at', 'updated_at'], 'safe'],
      [['image'], 'string', 'max' => 255],
      [['name'], 'string', 'max' => 100],
      [['sku'], 'string', 'max' => 50],
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
      'image' => 'Image',
      'name' => 'Name',
      'sku' => 'SKU',
      'serial' => 'Serial',
      'brand_id' => 'Brand',
      'category_id' => 'Category',
      'model_id' => 'Model',
      'description' => 'Description',
      'cost' => 'Cost',
      'price' => 'Price',
      'status' => 'Status',
      'available' => 'Available',
      'created_at' => 'Created At',
      'created_by' => 'Created By',
      'updated_at' => 'Updated At',
      'updated_by' => 'Updated By',
    ];
  }

  public function beforeSave($insert)
  {
    if (parent::beforeSave($insert)) {
      if ($this->isNewRecord) {
        $length = 5;
        $result = false;
        $sku = '';
        do {
          $sku = substr(
            str_shuffle(
              str_repeat(
                $x = '0123456789ABCDEFGHIJKLMNPQRSTUVWXYZ',
                ceil($length / strlen($x)),
              ),
            ),
            1,
            $length,
          );
          $has = self::findOne(['sku' => $sku]);
          if (empty($has)) {
            $result = true;
          }
        } while (!$result);
        $this->sku = $sku;
        $this->created_at = date('Y-m-d H:i:s');
        $this->created_by = Yii::$app->user->identity->id;
      } else {
        $this->updated_at = date('Y-m-d H:i:s');
        $this->updated_by = Yii::$app->user->identity->id;
      }
      return true;
    } else {
      return false;
    }
  }

  public function getStatusBadge()
  {
    if ($this->status == 1) {
      return '<span class="badge bg-info">Active</span>';
    } else {
      return '<span class="badge bg-danger">Inactive</span>';
    }
  }

  public function getBrand()
  {
    return $this->hasOne(ProductBrand::class, ['id' => 'brand_id']);
  }

  public function getCategory()
  {
    return $this->hasOne(ProductCategory::class, ['id' => 'category_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getInventories()
  {
    return $this->hasMany(Inventory::class, ['product_id' => 'id'])->orderBy([
      'created_at' => SORT_DESC,
    ]);
  }

  public function getVariations()
  {
    return $this->hasMany(ProductVariation::class, ['product_id' => 'id']);
  }

  public function getTotalCostValue()
  {
    $totalCost = 0;
    foreach ($this->variations as $variation) {
      $totalCost += $variation->cost;
    }
    return $totalCost;
  }

  public function getImagePath()
  {
    $placeholder = Yii::getAlias('@web') . Yii::$app->params['notFoundImage'];
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
      $filePath = 'uploads/products';
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
}
