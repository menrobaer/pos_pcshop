<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use yii\bootstrap5\Html;

AppAsset::register($this);
$this->registerJsFile(Yii::getAlias('@web/libs/particles.js/particles.js'), [
  'depends' => [AppAsset::class],
]);
$this->registerJsFile(Yii::getAlias('@web/js/pages/particles.app.js'), [
  'depends' => [AppAsset::class],
]);
$this->registerLinkTag([
  'rel' => 'icon',
  'type' => 'image/x-icon',
  'href' => Yii::getAlias('@web/images/favicon.ico'),
]);
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app
  ->language ?>" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

<head>
  <meta charset="<?= Yii::$app->charset ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <?php $this->registerCsrfMetaTags(); ?>
  <title><?= Html::encode($this->title) ?></title>
  <script src="<?= Yii::getAlias('@web/js/layout.js') ?>"></script>
  <?php $this->head(); ?>
</head>

<body>
  <?php $this->beginBody(); ?>

  <div class="auth-page-wrapper pt-5">
    <!-- auth page bg -->
    <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
      <div class="bg-overlay"></div>

      <div class="shape">
        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1440 120">
          <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
        </svg>
      </div>
    </div>

    <!-- auth page content -->
    <div class="auth-page-content">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="text-center mt-sm-5 mb-4 text-white-50">
              <div>
                <a href="<?= Yii::$app
                  ->homeUrl ?>" class="d-inline-block auth-logo">
                  <img src="<?= Yii::getAlias(
                    '@web/images/logo-light.png',
                  ) ?>" alt="" height="20">
                </a>
              </div>
              <p class="mt-3 fs-15 fw-medium">The Modern POS System</p>
            </div>
          </div>
        </div>
        <!-- end row -->

        <?= $content ?>

      </div>
      <!-- end container -->
    </div>
    <!-- end auth page content -->

    <!-- footer -->
    <footer class="footer">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="text-center">
              <p class="mb-0 text-muted">&copy;
                <script>
                  document.write(new Date().getFullYear())
                </script> VLC - POS. Crafted with <i class="mdi mdi-heart text-danger"></i> by Apple Tech
              </p>
            </div>
          </div>
        </div>
      </div>
    </footer>
    <!-- end Footer -->
  </div>
  <!-- end auth-page-wrapper -->

  <?php $this->endBody(); ?>
</body>

</html>
<?php $this->endPage(); ?>
