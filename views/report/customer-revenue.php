<?php

/** @var yii\web\View $this */
/** @var string $dateRange */
/** @var float $totalRevenue */
/** @var int $invoiceCount */
/** @var int $uniqueCustomers */
/** @var float $averageInvoice */
/** @var array $topCustomers */
/** @var app\models\Invoice[] $recentInvoices */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Customer Revenue Report';
$utils = Yii::$app->utils;
?>

<div class="row mb-4 align-items-center">
  <div class="col-lg-6">
    <h4 class="fs-18 mb-1">Customer Revenue</h4>
    <p class="text-muted mb-0">Track which customers generate the most revenue during the selected period.</p>
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
        <p class="text-uppercase fw-medium text-muted mb-1">Total Revenue</p>
        <h4 class="fs-22 mb-1"><?= $utils->dollarFormat($totalRevenue) ?></h4>
        <p class="text-muted mb-0 small"><?= Html::encode(
          $invoiceCount,
        ) ?> invoices</p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-height-100">
      <div class="card-body">
        <p class="text-uppercase fw-medium text-muted mb-1">Unique Customers</p>
        <h4 class="fs-22 mb-1"><?= Html::encode($uniqueCustomers) ?></h4>
        <p class="text-muted mb-0 small">Customers served</p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-height-100">
      <div class="card-body">
        <p class="text-uppercase fw-medium text-muted mb-1">Average Invoice</p>
        <h4 class="fs-22 mb-1"><?= $utils->dollarFormat($averageInvoice) ?></h4>
        <p class="text-muted mb-0 small">Revenue per invoice</p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-height-100">
      <div class="card-body">
        <p class="text-uppercase fw-medium text-muted mb-1">Range</p>
        <h4 class="fs-22 mb-1"><?= Html::encode($dateRange) ?></h4>
        <p class="text-muted mb-0 small">Selected period</p>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-xl-7">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Top Customers</h5>
        <small class="text-muted">Ranked by collected revenue</small>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col">Customer</th>
                <th scope="col">Revenue</th>
                <th scope="col">Invoices</th>
                <th scope="col">Avg/Invoice</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($topCustomers)): ?>
                <tr>
                  <td colspan="4" class="text-center text-muted py-4">No customer revenue available</td>
                </tr>
              <?php else: ?>
                <?php foreach ($topCustomers as $customer): ?>
                  <tr>
                    <td><?= Html::encode(
                      $customer['customer_name'] ?: 'Walk-in / Unknown',
                    ) ?></td>
                    <td><?= $utils->dollarFormat(
                      $customer['total_revenue'],
                    ) ?></td>
                    <td><?= Html::encode($customer['invoice_count']) ?></td>
                    <td><?= $utils->dollarFormat(
                      $customer['total_revenue'] /
                        max(1, $customer['invoice_count']),
                    ) ?></td>
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
        <h5 class="mb-0">Recent Invoices</h5>
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
                  <td colspan="3" class="text-center text-muted py-4">No invoices in range</td>
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
                    <td><?= $utils->dollarFormat($invoice->paid_amount) ?></td>
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

