<?php

/** @var yii\web\View $this */
/** @var string $dateRange */
/** @var float $totalRevenue */
/** @var float $totalExpenses */
/** @var float $netProfit */
/** @var int $invoiceCount */
/** @var int $expenseCount */
/** @var int $poCount */
/** @var array $dailyReport */
/** @var \app\models\Invoice[] $recentInvoices */
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
  <div class="col-md-4">
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
  <div class="col-md-4">
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
  <div class="col-md-4">
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
        <small class="text-muted">From <?= Html::encode(
          explode(' to ', $dateRange)[0],
        ) ?> to <?= Html::encode(explode(' to ', $dateRange)[1]) ?></small>
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
  <div class="col-xl-6">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Recent Invoices</h5>
        <a href="<?= Url::to([
          'invoice/index',
        ]) ?>" class="text-decoration-underline">All invoices</a>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-borderless align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col">Code</th>
                <th scope="col">Customer</th>
                <th scope="col">Date</th>
                <th scope="col">Amount</th>
                <th scope="col">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recentInvoices)): ?>
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">No invoices found</td>
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
                    <td><?= $utils->date(
                      $invoice->created_at,
                      'php:d M, Y',
                    ) ?></td>
                    <td><?= $utils->dollarFormat($invoice->paid_amount) ?></td>
                    <td><?= $invoice->getStatusLabel() ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-6">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Recent Expenses & Purchase Orders</h5>
        <a href="<?= Url::to([
          'expense/index',
        ]) ?>" class="text-decoration-underline">All expenses</a>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-borderless align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col">Code</th>
                <th scope="col">Supplier</th>
                <th scope="col">Date</th>
                <th scope="col">Amount</th>
                <th scope="col">Type</th>
                <th scope="col">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($financialRows)): ?>
                <tr>
                  <td colspan="6" class="text-center text-muted py-4">No expenses found</td>
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
                  $typeLabel = $isExpense ? 'Expense' : 'Purchase Order';
                  $badgeClass = $isExpense
                    ? 'badge bg-secondary-subtle text-secondary'
                    : 'badge bg-primary-subtle text-primary';
                  ?>
                  <tr>
                    <td>
                      <a href="<?= $link ?>" class="link-primary fw-semibold">
                        <?= Html::encode($model->code) ?>
                      </a>
                    </td>
                    <td><?= Html::encode($supplierName) ?></td>
                    <td><?= $utils->date($model->created_at) ?></td>
                    <td><?= $utils->dollarFormat($model->grand_total) ?></td>
                    <td>
                      <span class="<?= $badgeClass ?>"><?= Html::encode(
  $typeLabel,
) ?></span>
                    </td>
                    <td><?= $model->getStatusLabel() ?></td>
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

