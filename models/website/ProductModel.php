<?php

namespace app\models\website;

use Aws\S3\S3Client;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "product_model".
 *
 * @property int $id
 * @property string|null $name
 * @property int|null $status
 * @property string|null $image_url
 * @property int|null $sort
 */
class ProductModel extends ActiveRecord
{
    public $imageFile;

    const STATUS_ACTIVE = 1,
    STATUS_INACTIVE = 0,
    STATUS_DELETED = 10;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        // Adjust this if your actual database table name differs (e.g., 'model')
        return 'product_model';
    }

    /**
     * {@inheritdoc}
     */
    public static function getDb()
    {
        return Yii::$app->get('website');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            // Integer validation for status
            [['status','sort','brand_id'], 'integer'],

            // String length validations
            [['name'], 'string', 'max' => 50],
            [['image_url'], 'string', 'max' => 255],
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
            'name' => 'Name',
            'status' => 'Status',
            'image_url' => 'Image Url',
            'sort' => 'Sort',
            'brand_id' => 'Brand',
        ];
    }

    public function isUsed()
    {
        return false;
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


    public function getImagePath()
    {
        $placeholder = Yii::getAlias('@web') . (Yii::$app->params['notFoundImage'] ?? '/images/not_found_dummy.jpg');

        if (!$this->image_url) {
            return $placeholder;
        }

        if (filter_var($this->image_url, FILTER_VALIDATE_URL)) {
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
        $filePath = 'uploads/website/product-models';
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
                'region'  => $region,
                'credentials' => [
                    'key'    => $key,
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
            $modelId = (string) ($this->id ?: 0);
            $objectKey = $rootPrefix . '/product-model/' . $modelId . '/' . $fileName;

            $params = [
                'Bucket'     => $bucket,
                'Key'        => $objectKey,
                'SourceFile' => $this->imageFile->tempName,
                'ContentType' => $this->imageFile->type ?: 'application/octet-stream',
            ];

            if (!empty($config['acl'])) {
                $params['ACL'] = $config['acl'];
            }

            $s3Client->putObject($params);

            return $objectKey;
        } catch (\Throwable $e) {
            Yii::warning('S3 upload failed for ProductBrand: ' . $e->getMessage(), __METHOD__);
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
}