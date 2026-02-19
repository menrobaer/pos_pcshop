<?php

/** @var yii\web\View $this */
/** @var string $dateRange */
/** @var float $totalSales */
/** @var int $invoiceCount */
/** @var int $totalUnits */
/** @var array $dailySales */
/** @var array $topProducts */
/** @var app\models\Invoice[] $recentInvoices */

use app\components\Utils;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Sales Report';
?>

<div class="row mb-4 align-items-center">
  <div class="col-lg-6">
    <h4 class="fs-18 mb-1">Sales Report</h4>
    <p class="text-muted mb-0">Performance summary of orders, units sold, and average ticket size.</p>
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
        <p class="text-uppercase fw-medium text-muted mb-1">Total Sales</p>
        <h4 class="fs-22 mb-1"><?= Utils::dollarFormat($totalSales) ?></h4>
        <p class="text-muted mb-0 small">Total invoice value</p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-height-100">
      <div class="card-body">
        <p class="text-uppercase fw-medium text-muted mb-1">Orders</p>
        <h4 class="fs-22 mb-1"><?= Html::encode($invoiceCount) ?></h4>
        <p class="text-muted mb-0 small">Paid / processed</p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-height-100">
      <div class="card-body">
        <p class="text-uppercase fw-medium text-muted mb-1">Units Sold</p>
        <h4 class="fs-22 mb-1"><?= Html::encode($totalUnits) ?></h4>
        <p class="text-muted mb-0 small">Sum of invoice items</p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-height-100">
      <div class="card-body">
        <p class="text-uppercase fw-medium text-muted mb-1">Invoice Margins</p>
        <h4 class="fs-22 mb-1"><?= Utils::dollarFormat($totalMargin) ?></h4>
        <p class="text-muted mb-0 small">Sum of all invoices</p>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-xl-7">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Top Products</h5>
        <small class="text-muted">By units sold</small>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-borderless align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col">Product</th>
                <th scope="col">Units</th>
                <th scope="col">Revenue</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($topProducts)): ?>
                <tr>
                  <td colspan="3" class="text-center text-muted py-4">No product sales recorded</td>
                </tr>
              <?php else: ?>
                <?php foreach ($topProducts as $product): ?>
                  <tr>
                    <td><?= Html::encode(
                          $product['product_name'] ?: 'Unknown',
                        ) ?></td>
                    <td><?= Html::encode($product['quantity']) ?></td>
                    <td><?= Utils::dollarFormat($product['revenue']) ?></td>
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
        <h5 class="mb-0">Recent Sales</h5>
        <a href="<?= Url::to([
                    'invoice/index',
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
              <?php if (empty($recentInvoices)): ?>
                <tr>
                  <td colspan="3" class="text-center text-muted py-4">No sales in this range</td>
                </tr>
              <?php else: ?>
                <?php foreach ($recentInvoices as $invoice): ?>
                  <tr>
                    <td>
                      <a href="<?= Url::to([
                                  'invoice/view',
                                  'id' => $invoice->id,
                                ]) ?>" class="link-primary fw-semibold">
                        <?= Html::encode($invoice->code) ?>
                      </a>
                    </td>
                    <td><?= Html::encode(
                          $invoice->customer ? $invoice->customer->name : 'Walk-in',
                        ) ?></td>
                    <td><?= Utils::dollarFormat($invoice->grand_total) ?></td>
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
                <th scope="col">Sales</th>
                <th scope="col">Margins</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($dailySales)): ?>
                <tr>
                  <td colspan="3" class="text-center text-muted py-4">No daily data</td>
                </tr>
              <?php else: ?>
                <?php foreach ($dailySales as $day): ?>
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