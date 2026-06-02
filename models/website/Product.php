<?php

namespace app\models\website;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "product".
 *
 * @property int $id
 * @property int|null $category_id
 * @property int|null $brand_id
 * @property int|null $model_id
 * @property int|null $condition_id
 * @property string|null $source
 * @property string|null $sku
 * @property string|null $code
 * @property string|null $slug
 * @property string|null $name
 * @property float|null $price
 * @property string|null $full_price
 * @property string|null $image_url
 * @property string|null $warranty
 * @property string|null $description
 * @property string|null $free
 * @property string|null $stock
 * @property int|null $status
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 * @property float|null $cost
 * @property float|null $markup
 * @property int|null $available
 */
class Product extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product';
    }

    /**
     * Uses the `website` DB connection component.
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->get('website');
    }

     const STATUS_ACTIVE = 1,
        STATUS_INACTIVE = 0,
        STATUS_DELETED = 10;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name','slug'], 'required'],
            [['slug'], 'unique'],
            // Integer validations
            [['category_id', 'brand_id', 'model_id', 'condition_id', 'status', 'created_by', 'updated_by', 'available'], 'integer'],
            
            // Decimal / Number validations
            [['price', 'cost', 'markup'], 'number'],
            
            // Safe attributes for dates/text blocks
            [['description', 'created_at', 'updated_at'], 'safe'],
            
            // Default values
            [['price', 'cost', 'markup'], 'default', 'value' => 0.00],
            [['status'], 'default', 'value' => 1],
            [['available'], 'default', 'value' => 0],
            
            // String length validations
            [['sku'], 'string', 'max' => 5],
            [['source', 'code', 'slug', 'name', 'full_price', 'warranty', 'free', 'stock'], 'string', 'max' => 50],
            [['image_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_id' => 'Category',
            'brand_id' => 'Brand',
            'model_id' => 'Model',
            'condition_id' => 'Condition',
            'source' => 'Source',
            'sku' => 'SKU',
            'code' => 'Code',
            'slug' => 'Slug',
            'name' => 'Name',
            'price' => 'Price',
            'full_price' => 'Full Price',
            'image_url' => 'Image Url',
            'warranty' => 'Warranty',
            'description' => 'Description',
            'free' => 'Free',
            'stock' => 'Stock',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'cost' => 'Cost',
            'markup' => 'Markup',
            'available' => 'Available',
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

    public function getCategory()
    {
        return $this->hasOne(ProductCategory::class, ['id' => 'category_id']);
    }

    public function getBrand()
    {
        return $this->hasOne(ProductBrand::class, ['id' => 'brand_id']);
    }

    public function getModel()
    {
        return $this->hasOne(ProductModel::class, ['id' => 'model_id']);
    }

    public function getVariations()
    {
        return $this->hasMany(ProductVariation::class, ['product_id' => 'id']);
    }

    public function getStatusBadge()
    {
        if ($this->status == 1) {
        return '<span class="badge bg-info">Active</span>';
        } else {
        return '<span class="badge bg-danger">Inactive</span>';
        }
    }

    public function getImagePath()
    {
        $placeholder = Yii::getAlias('@web') . (Yii::$app->params['notFoundImage'] ?? '/images/not_found_dummy.jpg');

        $variations = $this->getVariations()
            ->andWhere(['not', ['image_url' => null]])
            ->andWhere(['<>', 'image_url', ''])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        foreach ($variations as $variation) {
            $resolvedPath = $this->resolveImagePath($variation->image_url);
            if ($resolvedPath !== null) {
                return $resolvedPath;
            }
        }

        $resolvedOwnPath = $this->resolveImagePath($this->image_url);
        if ($resolvedOwnPath !== null) {
            return $resolvedOwnPath;
        }

        return $placeholder;
    }

    private function resolveImagePath($path)
    {
        $path = trim((string) $path);

        if (!$path) {
            return null;
        }

        if ($this->isAbsoluteUrl($path)) {
            return $path;
        }

        $storageBaseUrl = $this->getStorageBaseUrl();
        if ($storageBaseUrl !== '') {
            return $storageBaseUrl . '/' . ltrim($path, '/');
        }

        if (!file_exists(Yii::getAlias('@webroot/' . $path))) {
            return null;
        }

        return Yii::getAlias('@web') . '/' . $path;
    }

    private function isAbsoluteUrl($path)
    {
        return preg_match('/^https?:\/\//i', (string) $path) === 1;
    }

    private function getStorageBaseUrl()
    {
        $storage = Yii::$app->params['storage'] ?? null;
        if (is_array($storage)) {
            return trim((string) ($storage['baseUrl'] ?? ''), '/');
        }

        $s3 = Yii::$app->params['s3'] ?? null;
        if (is_array($s3)) {
            return trim((string) ($s3['baseUrl'] ?? ''), '/');
        }

        return '';
    }
}