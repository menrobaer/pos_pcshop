<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 */
class AppAsset extends AssetBundle
{
  public $basePath = '@webroot';
  public $baseUrl = '@web';
  public $css = [
    'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
    'libs/swiper/swiper-bundle.min.css',
    'css/bootstrap.min.css',
    'css/icons.min.css',
    'css/app.min.css',
    'css/custom.min.css',
    'css/custom.css',
  ];
  public $js = [
    'libs/bootstrap/js/bootstrap.bundle.min.js',
    'libs/simplebar/simplebar.min.js',
    'libs/node-waves/waves.min.js',
    'libs/feather-icons/feather.min.js',
    'js/pages/plugins/lord-icon-2.1.0.js',
    // 'js/plugins.js',
    'https://cdn.jsdelivr.net/npm/toastify-js',
    'https://cdnjs.cloudflare.com/ajax/libs/choices.js/11.1.0/choices.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js',
    'libs/swiper/swiper-bundle.min.js',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11',
    'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
    'js/app.js',
    'js/custom.js',
  ];
  public $depends = ['yii\web\YiiAsset'];
}
