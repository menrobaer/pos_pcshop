<?php

namespace app\models\website;

use Aws\S3\S3Client;
use Yii;

/**
 * This is the model class for table "navigation_item".
 *
 * @property int $id
 * @property string|null $name
 * @property int|null $nav_id
 */
class NavigationItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'navigation_item';
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

    public $imageFile;
    public $category_id;
    public $brand_id;

    public function rules()
    {
        return [
            [['name', 'slug'], 'required'],
            [['nav_id', 'sort'], 'integer'],
            [['name', 'slug', 'color', 'background_color'], 'string', 'max' => 50],
            [['image_url'], 'string', 'max' => 255],
            [['imageFile'], 'image', 'skipOnEmpty' => true, 'extensions' => 'jpg, jpeg, gif, png, webp', 'maxSize' => 1024 * 1024 * 2],
            [['category_id', 'brand_id'], 'safe'],
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
            'nav_id' => 'Nav ID',
            'slug' => 'Slug',
            'sort' => 'Sort',
            'image_url' => 'Image',
        ];
    }

    public function getFolderPath()
    {
        /** @var \app\components\Master $master */
        $master = Yii::$app->master;
        return $master->getS3Path('navigation-item');
    }

    public function getImageKey()
    {
        $key = trim((string) $this->image_url);
        return $key === '' ? null : $key;
    }

    public function getUploadUrl()
    {
        return $this->getImagePath();
    }

    public function getThumbUploadUrl()
    {
        return $this->getImagePath();
    }

    public function getListUploadUrl()
    {
        return $this->getImagePath();
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
        $filePath = 'uploads/website/navigation-item';
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
            $itemId = (string) ($this->id ?: 0);
            $objectKey = $rootPrefix . '/navigation-item/' . $itemId . '/' . $fileName;

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
            return $objectKey;
        } catch (\Throwable $e) {
            Yii::warning('S3 upload failed for NavigationItem: ' . $e->getMessage(), __METHOD__);
            return false;
        }
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

    private function isAbsoluteUrl($path)
    {
        return preg_match('/^https?:\/\//i', (string) $path) === 1;
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

    public function getData()
    {
        return $this->hasMany(NavigationItemData::class, ['nav_item_id' => 'id']);
    }
}
