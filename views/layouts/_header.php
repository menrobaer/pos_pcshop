<?php

use app\models\User;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$user = Yii::$app->user->isGuest
  ? null
  : User::findOne(Yii::$app->user->identity->id);
?>
<header id="page-topbar">
  <div class="layout-width">
    <div class="navbar-header">
      <div class="d-flex">
        <div class="navbar-brand-box horizontal-logo">
          <a href="<?= Yii::$app->homeUrl ?>" class="logo logo-dark">
            <span class="logo-sm">
              <img src="<?= Yii::getAlias(
                '@web',
              ) ?>/images/logo-sm.png" alt="" height="22">
            </span>
            <span class="logo-lg">
              <img src="<?= Yii::getAlias(
                '@web',
              ) ?>/images/logo-dark.png" alt="" height="22">
            </span>
          </a>

          <a href="<?= Yii::$app->homeUrl ?>" class="logo logo-light">
            <span class="logo-sm">
              <img src="<?= Yii::getAlias(
                '@web',
              ) ?>/images/logo-sm.png" alt="" height="22">
            </span>
            <span class="logo-lg">
              <img src="<?= Yii::getAlias(
                '@web',
              ) ?>/images/logo-light.png" alt="" height="22">
            </span>
          </a>
        </div>

        <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger" id="topnav-hamburger-icon">
          <span class="hamburger-icon">
            <span></span>
            <span></span>
            <span></span>
          </span>
        </button>
      </div>

      <?php if ($user): ?>
        <div class="dropdown ms-sm-3 header-item topbar-user">
          <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="d-flex align-items-center">
              <img class="rounded-circle header-profile-user" src="<?= $user->getImagePath() ?>" alt="Header Avatar">
              <span class="text-start ms-xl-2">
                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text"><?= Html::encode(
                  $user->first_name . ' ' . $user->last_name,
                ) ?></span>
                <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text"><?= $user->role_id ==
                1
                  ? 'Admin'
                  : 'User' ?></span>
              </span>
            </span>
          </button>
          <div class="dropdown-menu dropdown-menu-end">
            <!-- item-->
            <h6 class="dropdown-header">Welcome <?= Html::encode(
              $user->first_name,
            ) ?>!</h6>
            <a class="dropdown-item" href="<?= Url::to([
              'user/profile',
            ]) ?>"><i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Profile</span></a>
            <!-- <a class="dropdown-item" href="apps-tasks-kanban.html"><i class="mdi mdi-calendar-check-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Taskboard</span></a> -->
            <div class="dropdown-divider"></div>
            <!-- <a class="dropdown-item" href="pages-profile-settings.html"><span class="badge bg-success-subtle text-success mt-1 float-end">New</span><i class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Settings</span></a> -->
            <a class="dropdown-item" href="#" id="header-logout" data-logout-url="<?= Url::to(
              ['site/logout'],
            ) ?>"><i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> <span class="align-middle" data-key="t-logout">Logout</span></a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</header>
<?php
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$this->registerJs(
  <<<JS
  function setupLogoutHandler() {
    var logoutLink = document.getElementById('header-logout');
    if (!logoutLink) {
      return;
    }
    
    function submitLogout(e) {
      e.preventDefault();
      e.stopPropagation();
      
      var logoutUrl = logoutLink.getAttribute('data-logout-url');
      var form = document.createElement('form');
      form.method = 'POST';
      form.action = logoutUrl;
      form.style.display = 'none';
      
      var csrfInput = document.createElement('input');
      csrfInput.type = 'hidden';
      csrfInput.name = "{$csrfParam}";
      csrfInput.value = "{$csrfToken}";
      form.appendChild(csrfInput);
      
      document.body.appendChild(form);
      form.submit();
    }
    
    logoutLink.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      
      if (typeof Swal === 'undefined') {
        submitLogout(e);
        return;
      }
      
      Swal.fire({
        title: 'Log out?',
        text: 'You will be signed out of this session.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, log out',
        cancelButtonText: 'Cancel',
      }).then(function (result) {
        if (result.isConfirmed) {
          submitLogout(e);
        }
      });
    });
  }
  
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupLogoutHandler);
  } else {
    setupLogoutHandler();
  }
  JS
  ,
);


?>
