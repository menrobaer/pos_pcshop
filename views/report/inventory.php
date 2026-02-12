<?php

/** @var yii\web\View $this */
/** @var string $dateRange */
/** @var int $incoming */
/** @var int $outgoing */
/** @var int $netMovement */
/** @var array $topProducts */
/** @var array $dailyMovements */
/** @var app\models\Inventory[] $recentLogs */

use app\components\Utils;
use yii\helpers\Html;

$formatter = Yii::$app->formatter;
$this->title = 'Inventory Report';
?>

<div class="row mb-4 align-items-center">
  <div class="col-lg-6">
    <h4 class="fs-18 mb-1">Inventory Report</h4>
    <p class="text-muted mb-0">Monitor how stock moves in and out over the selected period.</p>
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
        <p class="text-uppercase fw-medium text-muted mb-1">Stock In</p>
        <h4 class="fs-22 mb-1"><?= $formatter->asInteger($incoming) ?></h4>
        <p class="text-muted mb-0 small">Items received</p>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-height-100">
      <div class="card-body">
        <p class="text-uppercase fw-medium text-muted mb-1">Stock Out</p>
        <h4 class="fs-22 mb-1"><?= $formatter->asInteger($outgoing) ?></h4>
        <p class="text-muted mb-0 small">Items dispatched</p>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-height-100">
      <div class="card-body">
        <p class="text-uppercase fw-medium text-muted mb-1">Net Movement</p>
        <h4 class="fs-22 mb-1"><?= $formatter->asInteger($netMovement) ?></h4>
        <p class="text-muted mb-0 small">In minus out</p>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-xl-6">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Top Product Movement</h5>
        <small class="text-muted">Sorted by net movement</small>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col">Product</th>
                <th scope="col">In</th>
                <th scope="col">Out</th>
                <th scope="col">Net</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($topProducts)): ?>
                <tr>
                  <td colspan="4" class="text-center text-muted py-4">No movements recorded</td>
                </tr>
              <?php else: ?>
                <?php foreach ($topProducts as $product): ?>
                  <tr>
                    <td><?= Html::encode(
                      $product['product_name'] ?: 'Unknown',
                    ) ?></td>
                    <td><?= $formatter->asInteger($product['incoming']) ?></td>
                    <td><?= $formatter->asInteger($product['outgoing']) ?></td>
                    <td><?= $formatter->asInteger($product['net']) ?></td>
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
        <h5 class="mb-0">Recent Inventory Logs</h5>
        <small class="text-muted">Latest activity</small>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-borderless align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col">Product</th>
                <th scope="col">Type</th>
                <th scope="col">Movement</th>
                <th scope="col">Time</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recentLogs)): ?>
                <tr>
                  <td colspan="4" class="text-center text-muted py-4">No logs available</td>
                </tr>
              <?php else: ?>
                <?php foreach ($recentLogs as $log): ?>
                  <tr>
                    <td><?= Html::encode(
                      $log->product ? $log->product->name : 'Unknown',
                    ) ?></td>
                    <td><?= Html::encode($log->getTypeLabel()) ?></td>
                    <td>
                      In: <?= $formatter->asInteger($log->in) ?>,
                      Out: <?= $formatter->asInteger($log->out) ?>
                    </td>
                    <td><?= Utils::dateTime($log->created_at) ?></td>
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
        <h5 class="mb-0">Daily Inventory Totals</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col">Date</th>
                <th scope="col">Incoming</th>
                <th scope="col">Outgoing</th>
                <th scope="col">Net</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($dailyMovements)): ?>
                <tr>
                  <td colspan="4" class="text-center text-muted py-4">No daily movement data</td>
                </tr>
              <?php else: ?>
                <?php foreach ($dailyMovements as $day): ?>
                  <tr>
                    <td><?= Utils::date($day['period']) ?></td>
                    <td><?= $formatter->asInteger($day['incoming']) ?></td>
                    <td><?= $formatter->asInteger($day['outgoing']) ?></td>
                    <td><?= $formatter->asInteger($day['net']) ?></td>
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

