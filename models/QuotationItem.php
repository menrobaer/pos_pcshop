<?php

namespace app\models;

use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "quotation_item".
 *
 * @property int $id
 * @property int|null $quotation_id
 * @property int $product_id
 * @property string $product_name
 * @property string $sku
 * @property string $serial
 * @property string $unit
 * @property int $quantity
 * @property string $description
 * @property string|null $image_path
 * @property string $discount_type
 * @property float $full_price
 * @property float $discount
 * @property float $cost
 * @property float $price
 */
class QuotationItem extends \yii\db\ActiveRecord
{
  /** @var UploadedFile|null */
  public $imageFile;

  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'quotation_item';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [['quotation_id', 'product_id', 'quantity'], 'integer'],
      [['product_name', 'discount_type'], 'required'],
      [['description'], 'string'],
      [['image_path'], 'string', 'max' => 255],
      [['full_price', 'discount', 'cost', 'price'], 'default', 'value' => 0],
      [['full_price', 'discount', 'cost', 'price'], 'number'],
      [['product_name'], 'string', 'max' => 100],
      [['sku', 'serial'], 'string', 'max' => 50],
      [['unit'], 'string', 'max' => 20],
      [['discount_type'], 'string', 'max' => 10],
      [
        ['imageFile'],
        'image',
        'skipOnEmpty' => true,
        'extensions' => 'png, jpg, jpeg, gif, webp',
        'checkExtensionByMimeType' => true,
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
      'quotation_id' => 'Quotation ID',
      'product_id' => 'Product ID',
      'product_name' => 'Product Name',
      'sku' => 'SKU',
      'serial' => 'Serial',
      'unit' => 'Unit',
      'quantity' => 'Quantity',
      'description' => 'Description',
      'image_path' => 'Image',
      'discount_type' => 'Discount Type',
      'discount' => 'Discount',
      'full_price' => 'Full Price',
      'cost' => 'Cost',
      'price' => 'Price',
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getQuotation()
  {
    return $this->hasOne(Quotation::class, ['id' => 'quotation_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getProduct()
  {
    return $this->hasOne(Product::class, ['id' => 'product_id']);
  }

  public function uploadImage()
  {
    if (!$this->imageFile || !$this->validate(['imageFile'])) {
      return false;
    }

    $filePath = 'uploads/quotations/items';
    $directory = Yii::getAlias("@webroot/{$filePath}");

    if (!is_dir($directory)) {
      mkdir($directory, 0777, true);
    }

    $safeName = preg_replace('/[^A-Za-z0-9\-_]/', '-', $this->imageFile->baseName);
    $randomString = Yii::$app->security->generateRandomString(16);
    $fileName = $safeName . '-' . $randomString . '.' . $this->imageFile->extension;
    $path = $directory . '/' . $fileName;

    if ($this->imageFile->saveAs($path)) {
      return $filePath . '/' . $fileName;
    }

    return false;
  }

  public function getImageUrl()
  {
    if (!$this->image_path) {
      return null;
    }

    if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
      return $this->image_path;
    }

    $localPath = Yii::getAlias('@webroot/' . ltrim($this->image_path, '/'));
    if (!file_exists($localPath)) {
      return null;
    }

    return Yii::getAlias('@web') . '/' . ltrim($this->image_path, '/');
  }

  public function getDisplayImageUrl()
  {
    $itemImageUrl = $this->getImageUrl();
    if ($itemImageUrl !== null) {
      return $itemImageUrl;
    }

    if ($this->product && $this->product->image) {
      return $this->product->getImagePath();
    }

    return null;
  }

  public function deleteStoredImage(?string $imagePath = null)
  {
    $imagePath = trim((string) ($imagePath ?? $this->image_path));
    if ($imagePath === '' || filter_var($imagePath, FILTER_VALIDATE_URL)) {
      return false;
    }

    $localPath = Yii::getAlias('@webroot/' . ltrim($imagePath, '/'));
    if (!file_exists($localPath) || !is_file($localPath)) {
      return false;
    }

    return unlink($localPath);
  }
}
