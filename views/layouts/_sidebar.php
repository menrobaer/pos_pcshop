<?php

use yii\bootstrap5\Html;
use yii\helpers\Url;

$controller = Yii::$app->controller->id;
$action = Yii::$app->controller->action->id;
?>
<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
  <!-- LOGO -->
  <div class="navbar-brand-box">
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
    <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
      <i class="ri-record-circle-line"></i>
    </button>
  </div>

  <div id="scrollbar">
    <div class="container-fluid">
      <div id="two-column-menu">
      </div>
      <ul class="navbar-nav" id="navbar-nav">
        <li class="menu-title"><span>Menu</span></li>
        <li class="nav-item">
          <a class="nav-link menu-link <?= $controller == 'site'
            ? 'active'
            : '' ?>" href="<?= Yii::$app->homeUrl ?>">
            <i class="ri-dashboard-2-line"></i> <span>Dashboard</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link menu-link <?= $controller == 'product'
            ? 'active'
            : '' ?>" href="<?= Url::to(['product/index']) ?>">
            <i class="ri-apps-2-line"></i> <span>Products</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link menu-link <?= $controller == 'quotation'
            ? 'active'
            : '' ?>" href="<?= Url::to(['quotation/index']) ?>">
            <i class="ri-file-list-3-line"></i> <span>Quotations</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link menu-link <?= $controller == 'invoice'
            ? 'active'
            : '' ?>" href="<?= Url::to(['invoice/index']) ?>">
            <i class="ri-file-text-line"></i> <span>Invoices</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link menu-link <?= $controller == 'purchase-order'
            ? 'active'
            : '' ?>" href="<?= Url::to(['purchase-order/index']) ?>">
            <i class="ri-shopping-cart-2-line"></i> <span>Purchase Orders</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link menu-link <?= $controller == 'expense'
            ? 'active'
            : '' ?>" href="<?= Url::to(['expense/index']) ?>">
            <i class="ri-money-dollar-circle-line"></i> <span>Expenses</span>
          </a>
        </li>
        <?php $isPeople = in_array($controller, ['customer', 'supplier']); ?>
        <li class="nav-item">
          <a class="nav-link menu-link <?= $isPeople
            ? 'active'
            : '' ?>" href="#sidebarPeople" data-bs-toggle="collapse" role="button" aria-expanded="<?= $isPeople
  ? 'true'
  : 'false' ?>" aria-controls="sidebarPeople">
            <i class="ri-team-line"></i> <span>People</span>
          </a>
          <div class="collapse menu-dropdown <?= $isPeople
            ? 'show'
            : '' ?>" id="sidebarPeople">
            <ul class="nav nav-sm flex-column">
              <li class="nav-item">
                <a href="<?= Url::to([
                  'customer/index',
                ]) ?>" class="nav-link <?= $controller == 'customer'
  ? 'active'
  : '' ?>">Customers</a>
              </li>
              <li class="nav-item">
                <a href="<?= Url::to([
                  'supplier/index',
                ]) ?>" class="nav-link <?= $controller == 'supplier'
  ? 'active'
  : '' ?>">Suppliers</a>
              </li>
            </ul>
          </div>
        </li>
        <?php $isReport = $controller == 'report'; ?>
        <li class="nav-item">
          <a class="nav-link menu-link <?= $isReport
            ? 'active'
            : '' ?>" href="#sidebarReports" data-bs-toggle="collapse" role="button" aria-expanded="<?= $isReport
  ? 'true'
  : 'false' ?>" aria-controls="sidebarReports">
            <i class="ri-bar-chart-line"></i> <span>Report</span>
          </a>
          <div class="collapse menu-dropdown <?= $isReport
            ? 'show'
            : '' ?>" id="sidebarReports">
            <ul class="nav nav-sm flex-column">
              <li class="nav-item">
                <a href="<?= Url::to([
                  'report/financial',
                ]) ?>" class="nav-link <?= $controller == 'report' &&
$action == 'financial'
  ? 'active'
  : '' ?>">Financial</a>
              </li>
              <li class="nav-item">
                <a href="<?= Url::to([
                  'report/customer-revenue',
                ]) ?>" class="nav-link <?= $controller == 'report' &&
$action == 'customer-revenue'
  ? 'active'
  : '' ?>">Customer Revenue</a>
              </li>
              <li class="nav-item">
                <a href="<?= Url::to([
                  'report/sales',
                ]) ?>" class="nav-link <?= $controller == 'report' &&
$action == 'sales'
  ? 'active'
  : '' ?>">Sales</a>
              </li>
              <li class="nav-item">
                <a href="<?= Url::to([
                  'report/inventory',
                ]) ?>" class="nav-link <?= $controller == 'report' &&
$action == 'inventory'
  ? 'active'
  : '' ?>">Inventory</a>
              </li>
            </ul>
          </div>
        </li>
        <?php $isSetting = in_array($controller, [
          'product-category',
          'product-brand',
          'user',
          'user-permission',
          'outlet',
        ]); ?>
        <li class="nav-item">
          <a class="nav-link menu-link <?= $isSetting
            ? 'active'
            : '' ?>" href="#sidebarSettings" data-bs-toggle="collapse" role="button" aria-expanded="<?= $isSetting
  ? 'true'
  : 'false' ?>" aria-controls="sidebarSettings">
            <i class="ri-settings-2-line"></i> <span>Setting</span>
          </a>
          <div class="collapse menu-dropdown <?= $isSetting
            ? 'show'
            : '' ?>" id="sidebarSettings">
            <ul class="nav nav-sm flex-column">
              <li class="nav-item">
                <a href="<?= Url::to([
                  'product-category/index',
                ]) ?>" class="nav-link <?= $controller == 'product-category'
  ? 'active'
  : '' ?>">Product Category</a>
              </li>
              <li class="nav-item">
                <a href="<?= Url::to([
                  'product-brand/index',
                ]) ?>" class="nav-link <?= $controller == 'product-brand'
  ? 'active'
  : '' ?>">Product Brand</a>
              </li>
              <li class="nav-item">
                <a href="<?= Url::to([
                  'user/index',
                ]) ?>" class="nav-link <?= $controller == 'user'
  ? 'active'
  : '' ?>">Users</a>
              </li>
              <li class="nav-item">
                <a href="<?= Url::to([
                  'user-permission/index',
                ]) ?>" class="nav-link <?= $controller == 'user-permission'
  ? 'active'
  : '' ?>">User Permission</a>
              </li>
              <li class="nav-item">
                <a href="<?= Url::to([
                  'outlet/index',
                ]) ?>" class="nav-link <?= $controller == 'outlet'
  ? 'active'
  : '' ?>">Outlet</a>
              </li>
            </ul>
          </div>
        </li>
      </ul>
    </div>
    <!-- Sidebar -->
  </div>

  <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->