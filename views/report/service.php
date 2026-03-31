<?php

/** @var yii\web\View $this */
/** @var string $dateRange */
/** @var float $totalServices */
/** @var int $serviceCount */
/** @var int $totalUnits */
/** @var array $dailyServices */
/** @var array $topServices */
/** @var app\models\Service[] $recentServices */

use app\components\Utils;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Service Report';
?>

<div class="row mb-4 align-items-center">
  <div class="col-lg-6">
    <h4 class="fs-18 mb-1">Service Report</h4>
    <p class="text-muted mb-0">Performance summary of service orders, units provided, and revenue margins.</p>
  </div>
  <div class="col-lg-6">
    <form method="get" class="d-flex justify-content-lg-end mt-3 mt-lg-0">
      <div class="input-group" style="max-width: 300px;">
        <input
          type="text"
          name="date_range"
          class="form-control border-0 dash-filter-picker shadow"
          data-provider="flatpickr"
          data-range-date="true"
          data-date-format="d M, Y"
          value="<?= Html::encode($dateRange) ?>">
        <button type="submit" class="input-group-text bg-primary border-primary text-white">
          <i class="ri-calendar-2-line"></i>
        </button>
      </div>
    </form>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card card-height-100">
      <div class="card-body">
        <p class="text-uppercase fw-medium text-muted mb-1">Total Services</p>
        <h4 class="fs-22 mb-1"><?= Utils::dollarFormat($totalServices) ?></h4>
        <p class="text-muted mb-0 small">Total service value</p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-height-100">
      <div class="card-body">
        <p class="text-uppercase fw-medium text-muted mb-1">Orders</p>
        <h4 class="fs-22 mb-1"><?= Html::encode($serviceCount) ?></h4>
        <p class="text-muted mb-0 small">Paid / processed</p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-height-100">
      <div class="card-body">
        <p class="text-uppercase fw-medium text-muted mb-1">Units Provided</p>
        <h4 class="fs-22 mb-1"><?= Html::encode($totalUnits) ?></h4>
        <p class="text-muted mb-0 small">Sum of service items</p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-height-100">
      <div class="card-body">
        <p class="text-uppercase fw-medium text-muted mb-1">Service Margins</p>
        <h4 class="fs-22 mb-1"><?= Utils::dollarFormat($totalMargin) ?></h4>
        <p class="text-muted mb-0 small">Sum of all services</p>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-xl-7">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Top Services</h5>
        <small class="text-muted">By units provided</small>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-borderless align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col">Service</th>
                <th scope="col">Units</th>
                <th scope="col">Revenue</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($topServices)): ?>
                <tr>
                  <td colspan="3" class="text-center text-muted py-4">No service sales recorded</td>
                </tr>
              <?php else: ?>
                <?php foreach ($topServices as $service): ?>
                  <tr>
                    <td><?= Html::encode(
                          $service['name'] ?: 'Unknown',
                        ) ?></td>
                    <td><?= Html::encode($service['quantity']) ?></td>
                    <td><?= Utils::dollarFormat($service['revenue']) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-5">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Recent Services</h5>
        <a href="<?= Url::to([
                    'service/index',
                  ]) ?>" class="text-decoration-underline">View all</a>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-borderless align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col">Code</th>
                <th scope="col">Customer</th>
                <th scope="col">Amount</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recentServices)): ?>
                <tr>
                  <td colspan="3" class="text-center text-muted py-4">No services in this range</td>
                </tr>
              <?php else: ?>
                <?php foreach ($recentServices as $service): ?>
                  <tr>
                    <td>
                      <a href="<?= Url::to([
                                  'service/view',
                                  'id' => $service->id,
                                ]) ?>" class="link-primary fw-semibold">
                        <?= Html::encode($service->code) ?>
                      </a>
                    </td>
                    <td><?= Html::encode(
                          $service->customer ? $service->customer->name : 'Walk-in',
                        ) ?></td>
                    <td><?= Utils::dollarFormat($service->grand_total) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row mt-4">
  <div class="col-xl-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Daily Orders</h5>
        <small class="text-muted">Breakdown by date</small>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col">Date</th>
                <th scope="col">Orders</th>
                <th scope="col">Services</th>
                <th scope="col">Margins</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($dailyServices)): ?>
                <tr>
                  <td colspan="4" class="text-center text-muted py-4">No daily data</td>
                </tr>
              <?php else: ?>
                <?php foreach ($dailyServices as $day): ?>
                  <tr>
                    <td><?= Utils::date($day['period']) ?></td>
                    <td><?= Html::encode($day['orders']) ?></td>
                    <td><?= Utils::dollarFormat($day['total']) ?></td>
                    <td><?= Utils::dollarFormat($day['margins']) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>