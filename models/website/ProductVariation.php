<?php

namespace app\models\website;

use Aws\S3\S3Client;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "product_variation".
 *
 * @property int $id
 * @property int $product_id
 * @property string|null $name
 * @property float|null $price
 * @property string|null $image_url
 * @property float|null $cost
 * @property int|null $available
 * @property float|null $dealer_price
 * @property int|null $source_id
 * @property int|null $stock_id
 * @property int|null $warranty_id
 * @property int|null $status
 * @property string|null $code
 * @property string|null $description
 * @property string|null $free
 * @property string|null $custom_url
 */
class ProductVariation extends ActiveRecord
{
    public $imageFile;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        // Adjust this if your actual database table name differs
        return 'product_variation';
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
            [['price'],'required'],
            [['price'],'number','min' => 1],
            // Integer validations (including tinyint columns)
            [['product_id', 'available', 'source_id', 'stock_id', 'warranty_id', 'status'], 'integer'],

            // Decimal / Number validations
            [['price', 'cost', 'dealer_price'], 'number'],

            // Default value configurations
            [['price', 'cost', 'dealer_price'], 'default', 'value' => 0.00],
            [['available'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => 1],

            // String length validations
            [['code'], 'string', 'max' => 20],
            [['name', 'free'], 'string', 'max' => 50],
            [['image_url', 'description'], 'string', 'max' => 255],
            [['custom_url'], 'string', 'max' => 500],
            [['imageFile'], 'image', 'skipOnEmpty' => true, 'extensions' => 'jpg, jpeg, gif, png, webp', 'maxSize' => 1024 * 1024 * 2],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product',
            'name' => 'Name',
            'price' => 'Price',
            'image_url' => 'Image Url',
            'cost' => 'Cost',
            'available' => 'Available',
            'dealer_price' => 'Dealer Price',
            'source_id' => 'Source',
            'stock_id' => 'Stock',
            'warranty_id' => 'Warranty',
            'status' => 'Status',
            'code' => 'Code',
            'description' => 'Description',
            'free' => 'Free',
            'custom_url' => 'Custom Url',
        ];
    }

    public function getImagePath()
    {
        $placeholder = Yii::getAlias('@web') . (Yii::$app->params['notFoundImage'] ?? '/images/not_found_dummy.jpg');

        if (!$this->image_url) {
            return $placeholder;
        }

        if ($this->isAbsoluteUrl($this->image_url)) {
            return $this->image_url;
        }

        $storageBaseUrl = $this->getStorageBaseUrl();
        if ($storageBaseUrl !== '') {
            return $storageBaseUrl . '/' . ltrim($this->image_url, '/');
        }

        if (!file_exists(Yii::getAlias('@webroot/' . $this->image_url))) {
            return $placeholder;
        }

        return Yii::getAlias('@web') . '/' . $this->image_url;
    }

    public function uploadImage()
    {
        if (!$this->imageFile || !$this->validate(['imageFile'])) {
            return false;
        }

        $s3Path = $this->uploadImageToS3();
        if ($s3Path !== false) {
            return $s3Path;
        }

        return $this->uploadImageToLocal();
    }

    private function uploadImageToLocal()
    {
        $filePath = 'uploads/website/product-variations';
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

    private function uploadImageToS3()
    {
        $config = $this->getStorageConfig();
        $enabled = !empty($config['enabled']);
        $bucket = $config['bucket'] ?? null;
        $region = $config['region'] ?? null;
        $key = $config['key'] ?? null;
        $secret = $config['secret'] ?? null;

        if (!$enabled || !$bucket || !$region || !$key || !$secret) {
            return false;
        }

        try {
            $clientConfig = [
                'version' => 'latest',
                'region' => $region,
                'credentials' => [
                    'key' => $key,
                    'secret' => $secret,
                ],
            ];

            $endpoint = $this->resolveStorageEndpoint($config, (string) $bucket);
            if ($endpoint !== null) {
                $clientConfig['endpoint'] = $endpoint;
            }

            if (array_key_exists('usePathStyleEndpoint', $config)) {
                $clientConfig['use_path_style_endpoint'] = (bool) $config['usePathStyleEndpoint'];
            }

            $s3Client = new S3Client($clientConfig);

            $rootPrefix = trim((string) ($config['prefix'] ?? 'vlc'), '/');
            if ($rootPrefix === '') {
                $rootPrefix = 'vlc';
            }

            $safeName = preg_replace('/[^A-Za-z0-9\-_]/', '-', $this->imageFile->baseName);
            $randomString = Yii::$app->security->generateRandomString(10);
            $fileName = $safeName . '-' . $randomString . '.' . $this->imageFile->extension;
            $productId = (string) ($this->product_id ?: ($this->id ?: 0));
            $objectKey = $rootPrefix . '/product/' . $productId . '/' . $fileName;

            $params = [
                'Bucket' => $bucket,
                'Key' => $objectKey,
                'SourceFile' => $this->imageFile->tempName,
                'ContentType' => $this->imageFile->type ?: 'application/octet-stream',
            ];

            if (!empty($config['acl'])) {
                $params['ACL'] = $config['acl'];
            }

            $s3Client->putObject($params);

            // Store only the object key in DB, e.g. vlc/product-variation/977/file.png
            return $objectKey;
        } catch (\Throwable $e) {
            Yii::warning('S3 upload failed for ProductVariation: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    private function getStorageConfig()
    {
        $storage = Yii::$app->params['storage'] ?? null;
        if (is_array($storage)) {
            return $storage;
        }

        $s3 = Yii::$app->params['s3'] ?? null;
        return is_array($s3) ? $s3 : [];
    }

    private function getStorageBaseUrl()
    {
        $config = $this->getStorageConfig();
        return trim((string) ($config['baseUrl'] ?? ''), '/');
    }

    private function resolveStorageEndpoint(array $config, string $bucket)
    {
        $endpoint = trim((string) ($config['endpoint'] ?? ''));
        if ($endpoint !== '') {
            return $endpoint;
        }

        $baseUrl = trim((string) ($config['baseUrl'] ?? ''));
        if ($baseUrl === '' || $bucket === '') {
            return null;
        }

        $scheme = parse_url($baseUrl, PHP_URL_SCHEME) ?: 'https';
        $host = parse_url($baseUrl, PHP_URL_HOST);
        if (!is_string($host)) {
            return null;
        }

        $prefix = $bucket . '.';
        if (stripos($host, $prefix) !== 0) {
            return null;
        }

        $rootHost = substr($host, strlen($prefix));
        return $rootHost === '' ? null : $scheme . '://' . $rootHost;
    }

    private function isAbsoluteUrl($path)
    {
        return preg_match('/^https?:\/\//i', (string) $path) === 1;
    }
}