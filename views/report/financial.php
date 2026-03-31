<?php

/** @var yii\web\View $this */
/** @var string $dateRange */
/** @var float $totalRevenue */
/** @var float $invoiceRevenue */
/** @var float $serviceRevenue */
/** @var float $totalExpenses */
/** @var float $netProfit */
/** @var int $invoiceCount */
/** @var int $serviceCount */
/** @var int $expenseCount */
/** @var int $poCount */
/** @var array $dailyReport */
/** @var \app\models\Invoice[] $recentInvoices */
/** @var \app\models\Service[] $recentServices */
/** @var array $financialRows */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Financial Report';
$utils = Yii::$app->utils;
?>

<div class="row mb-4 align-items-center">
  <div class="col-lg-6">
    <h4 class="fs-18 mb-1">Financial Report</h4>
    <p class="text-muted mb-0">Overview of revenue, spending, and net performance within the selected period.</p>
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
  <div class="col-md-6">
    <div class="card card-height-100">
      <div class="card-body">
        <p class="text-uppercase fw-medium text-muted mb-1">Total Revenue</p>
        <h4 class="fs-22 mb-2"><?= $utils->dollarFormat($totalRevenue) ?></h4>
        <div class="small">
          <p class="mb-1">
            <span class="d-inline-block" style="width: 8px; height: 8px; border-radius: 50%; background-color: #0d6efd; vertical-align: middle;"></span>
            Invoices: <?= Html::encode($invoiceCount) ?> (<?= $utils->dollarFormat($invoiceRevenue) ?>)
          </p>
          <p class="mb-0">
            <span class="d-inline-block" style="width: 8px; height: 8px; border-radius: 50%; background-color: #198754; vertical-align: middle;"></span>
            Services: <?= Html::encode($serviceCount) ?> (<?= $utils->dollarFormat($serviceRevenue) ?>)
          </p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-height-100">
      <div class="card-body">
        <p class="text-uppercase fw-medium text-muted mb-1">Total Expenses</p>
        <h4 class="fs-22 mb-1"><?= $utils->dollarFormat($totalExpenses) ?></h4>
        <p class="text-muted mb-0 small">
          <?= Html::encode($expenseCount) ?> payouts ·
          <?= Html::encode($poCount) ?> purchase orders
        </p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-height-100">
      <div class="card-body">
        <p class="text-uppercase fw-medium text-muted mb-1">Net Profit</p>
        <h4 class="fs-22 mb-1"><?= $utils->dollarFormat($netProfit) ?></h4>
        <p class="text-muted mb-0 small">Difference between revenue and expense</p>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-xl-12">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Daily Movement</h5>
        <small class="text-muted">
          <?php
          $parts = explode(' to ', $dateRange);
          if (count($parts) === 2) {
            echo 'From ' . Html::encode($parts[0]) . ' to ' . Html::encode($parts[1]);
          } else {
            echo Html::encode($dateRange);
          }
          ?>
        </small>
      </div>
      <div class="card-body">
        <div class="table-responsive table-card">
          <table class="table table-striped table-borderless align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col">Date</th>
                <th scope="col">Revenue</th>
                <th scope="col">Expenses</th>
                <th scope="col">Net</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($dailyReport)): ?>
                <tr>
                  <td colspan="4" class="text-center text-muted py-4">No data for the selected range</td>
                </tr>
              <?php else: ?>
                <?php foreach ($dailyReport as $row): ?>
                  <tr>
                    <td><?= Html::encode($row['label']) ?></td>
                    <td><?= $utils->dollarFormat($row['revenue']) ?></td>
                    <td><?= $utils->dollarFormat($row['expense']) ?></td>
                    <td><?= $utils->dollarFormat($row['net']) ?></td>
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
  <div class="col-xl-4">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Recent Invoices</h5>
        <a href="<?= Url::to([
                    'invoice/index',
                  ]) ?>" class="text-decoration-underline">All</a>
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
                  <td colspan="3" class="text-center text-muted py-4">No invoices found</td>
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
                    <td class="small"><?= Html::encode(
                                        $invoice->customer ? $invoice->customer->name : 'Walk-in',
                                      ) ?></td>
                    <td class="small text-end"><?= $utils->dollarFormat($invoice->paid_amount) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-4">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Recent Services</h5>
        <a href="<?= Url::to([
                    'service/index',
                  ]) ?>" class="text-decoration-underline">All</a>
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
                  <td colspan="3" class="text-center text-muted py-4">No services found</td>
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
                    <td class="small"><?= Html::encode(
                                        $service->customer ? $service->customer->name : 'Walk-in',
                                      ) ?></td>
                    <td class="small text-end"><?= $utils->dollarFormat($service->paid_amount) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-4">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Recent Expenses & POs</h5>
        <a href="<?= Url::to([
                    'expense/index',
                  ]) ?>" class="text-decoration-underline">All</a>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-borderless align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col">Code</th>
                <th scope="col">Supplier</th>
                <th scope="col">Amount</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($financialRows)): ?>
                <tr>
                  <td colspan="3" class="text-center text-muted py-4">No expenses found</td>
                </tr>
              <?php else: ?>
                <?php foreach ($financialRows as $row):

                  $isExpense = $row['type'] === 'expense';
                  $model = $row['model'];
                  $link = Url::to([
                    $isExpense ? 'expense/view' : 'purchase-order/view',
                    'id' => $model->id,
                  ]);
                  $supplierName = $model->supplier
                    ? $model->supplier->name
                    : 'Unknown';
                ?>
                  <tr>
                    <td>
                      <a href="<?= $link ?>" class="link-primary fw-semibold">
                        <?= Html::encode($model->code) ?>
                      </a>
                    </td>
                    <td class="small"><?= Html::encode($supplierName) ?></td>
                    <td class="small text-end"><?= $utils->dollarFormat($model->grand_total) ?></td>
                  </tr>
                <?php
                endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>