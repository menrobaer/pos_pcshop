<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Quotation $model */

$this->title = 'Quotation Details: ' . $model->code;
$this->params['breadcrumbs'][] = ['label' => 'Quotations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
// calculate margin totals
$totalCost = 0.0;
foreach ($model->items as $it) {
  $costPer =
    $it->product && isset($it->product->cost)
    ? (float) $it->product->cost
    : 0.0;
  $qty = isset($it->quantity) ? (float) $it->quantity : 0.0;
  $totalCost += $costPer * $qty;
}
$totalSale = (float) $model->grand_total;
$totalMargin = $totalSale - $totalCost;
$marginPercent = $totalSale > 0 ? ($totalMargin / $totalSale) * 100 : 0;

/** @var app\components\Utils $utils */
$utils = Yii::$app->utils;
?>
<style>
  .invoice-signature .col-3 {
    margin-top: 2rem;
    height: 4rem;
    border-bottom: 1px solid #333;
  }

  #barcode-container {
    position: relative !important;
    width: fit-content !important;
    margin-left: auto !important;
  }

  #barcode-container>div:first-child {
    display: flex !important;
  }

  #quotation-code {
    background-color: #fff !important;
    position: absolute !important;
    top: 75% !important;
    left: 50% !important;
    transform: translateX(-50%) !important;
    z-index: 10 !important;
    white-space: nowrap !important;
    padding: 2px 4px !important;
  }

  .quotation-title {
    font-size: 18px !important;
  }
</style>
<div class="quotation-view">
  <div class="row">
    <div class="col-xxl-9">
      <div class="card" id="demo">
        <div class="row">
          <div class="col-lg-12">
            <div class="card-header border-bottom-dashed p-4">
              <div class="d-flex justify-content-end">
                <div class="flex-shrink-0 mt-sm-0 mt-3 text-end">
                  <div class="mb-4 d-print-none" data-html2canvas-ignore="true">
                    <?php if (
                      $model->status !=
                      \app\models\Quotation::STATUS_CANCELLED &&
                      $model->status !=
                      \app\models\Quotation::STATUS_ACCEPTED
                    ): ?>
                      <?= Html::a(
                        '<i class="ri-edit-line align-bottom me-1"></i> Update',
                        ['update', 'id' => $model->id],
                        [
                          'class' =>
                          'btn btn-primary btn-sm',
                        ],
                      ) ?>
                    <?php else: ?>
                      <?= Html::button(
                        '<i class="ri-edit-line align-bottom me-1"></i> Update',
                        [
                          'class' =>
                          'btn btn-primary btn-sm disabled',
                          'title' =>
                          'Quotation is not editable after cancel/convert.',
                        ],
                      ) ?>
                    <?php endif; ?>
                    <?= Html::a(
                      '<i class="ri-delete-bin-line align-bottom me-1"></i> Delete',
                      ['delete', 'id' => $model->id],
                      [
                        'class' => 'btn btn-danger btn-sm',
                        'data' => [
                          'confirm' =>
                          'Are you sure you want to delete this item?',
                          'method' => 'post',
                        ],
                      ],
                    ) ?>

                    <div class="btn-group">
                      <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        Actions
                      </button>
                      <ul class="dropdown-menu">
                        <li>
                          <a class="dropdown-item quotation-action" href="#" data-action-url="<?= Url::to(
                                                                                                [
                                                                                                  'quotation/duplicate',
                                                                                                  'id' => $model->id,
                                                                                                ],
                                                                                              ) ?>" data-action-title="Duplicate Quotation" data-action-text="This will create a new quotation draft with the same items. Continue?">Duplicate</a>
                        </li>
                        <li>
                          <a class="dropdown-item quotation-action" href="#" data-action-url="<?= Url::to(
                                                                                                [
                                                                                                  'quotation/cancel',
                                                                                                  'id' => $model->id,
                                                                                                ],
                                                                                              ) ?>" data-action-title="Cancel Quotation" data-action-text="This will cancel the quotation and prevent further changes. Continue?">Cancel</a>
                        </li>
                      </ul>
                    </div>

                    <a href="javascript:void(0)" class="btn btn-soft-info btn-sm" id="btn-print-quotation"><i class="ri-printer-line align-bottom me-1"></i> Print</a>
                  </div>
                </div>
              </div>
            </div>
            <!--end card-header-->
          </div><!--end col-->
          <div class="col-lg-12">
            <div class="card-body p-4 border-top border-top-dashed">
              <div class="row g-3">
                <div class="col-4">
                  <p class="fw-bold mb-2">Quote To: <?= $model->customer ? Html::encode($model->customer->name) : '-' ?></p>
                  <p class="text-muted mb-0"><span>Phone: </span><?= $model->phone ? Html::encode($model->phone) : '-' ?></p>
                  <p class="text-muted mb-1"><span>Address: </span><?= $model->address ? Html::encode($model->address) : '-' ?></p>
                </div>
                <div class="col-4">
                  <div class="text-center">
                    <h3 class="fw-bold mb-0 quotation-title">សម្រង់តម្លៃ<br>Quotation</h3>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div id="barcode-container" class="mb-3">
                    <div><svg id="quotation-barcode"></svg></div>
                    <div id="quotation-code"><?= $model->code ?></div>
                  </div>
                  <p class="mb-0"><span class="text-muted">Date:</span> <?= $utils->date($model->date) ?></p>
                </div>
              </div>
            </div>
          </div><!--end col-->
          <div class="col-lg-12">
            <div class="card-body p-4">
              <div class="table-responsive">
                <table class="table table-borderless text-center align-top mb-0">
                  <thead>
                    <tr class="table-active">
                      <th scope="col" style="width: 50px;">#</th>
                      <th scope="col" style="width: 90px;">Image</th>
                      <th scope="col" class="text-start" style="min-width: 250px;">Product Details</th>
                      <th scope="col" style="min-width: 60px;">Price</th>
                      <th scope="col" style="min-width: 50px;">Quantity</th>
                      <th scope="col" class="text-end" style="min-width: 60px;">Amount</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($model->items as $index => $item): ?>
                      <tr>
                        <th scope="row"><?= str_pad(
                                          $index + 1,
                                          2,
                                          '0',
                                          STR_PAD_LEFT,
                                        ) ?></th>
                        <td class="text-center">
                          <?php
                          $product = $item->product;
                          if ($product && $product->image): ?>
                            <?= Html::img($product->getImagePath(), [
                              'style' => 'max-width: 80px; max-height: 80px; object-fit: contain;',
                              'alt' => $item->product_name,
                              'class' => 'img-thumbnail',
                            ]) ?>
                          <?php else: ?>
                            <span class="text-muted small">No Image</span>
                          <?php endif; ?>
                        </td>
                        <td class="text-start">
                          <?= Html::a(
                            Html::tag('span', Html::encode($item->product_name), ['class' => 'fw-bold']),
                            ['product/view', 'id' => $item->product_id],
                            ['class' => 'text-dark link-primary']
                          ) ?>
                          <div class="d-flex align-items-center mt-2">
                            <div class="flex-grow-1">
                              <?php if ($item->serial): ?>
                                <p class="text-muted mb-1"><span class="fw-medium">Serial:</span> <?= Html::encode($item->serial) ?></p>
                              <?php endif; ?>
                              <?php if ($item->sku): ?>
                                <p class="text-muted mb-1"><span class="fw-medium">SKU:</span> <?= Html::encode($item->sku) ?></p>
                              <?php endif; ?>
                              <?php if ($item->description): ?>
                                <p class="text-muted mb-0"><?= nl2br(
                                                              Html::encode($item->description),
                                                            ) ?></p>
                              <?php endif; ?>
                            </div>
                          </div>
                        </td>
                        <td class="d-none"><?= Html::encode($item->serial ?: '-') ?></td>
                        <td>
                          $<?= number_format($item->price, 2) ?>
                          <?php if ($item->discount > 0): ?>
                            <div class="text-danger small">
                              - <?= $item->discount_type === 'percentage'
                                  ? number_format($item->discount, 0) . '%'
                                  : '$' . number_format($item->discount, 2) ?>
                            </div>
                          <?php endif; ?>
                        </td>
                        <td><?= $item->quantity ?></td>
                        <td class="text-end">
                          <div class="fw-bold">$<?= number_format(
                                                  $item->quantity * $item->price,
                                                  2,
                                                ) ?></div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table><!--end table-->
              </div>
              <div class="border-top border-top-dashed mt-2">
                <table class="table table-borderless table-nowrap align-middle mb-0 ms-auto" style="width:250px">
                  <tbody>
                    <tr>
                      <td>Sub Total</td>
                      <td class="text-end">$<?= number_format(
                                              $model->sub_total,
                                              2,
                                            ) ?></td>
                    </tr>
                    <?php if (
                      $model->discount_amount > 0
                    ): ?>
                      <tr>
                        <td>Discount</td>
                        <td class="text-end text-danger">- $<?= number_format(
                                                              $model->discount_amount,
                                                              2,
                                                            ) ?></td>
                      </tr>
                    <?php endif; ?>
                    <?php if ($model->delivery_fee > 0): ?>
                      <tr>
                        <td>Delivery Fee</td>
                        <td class="text-end">$<?= number_format(
                                                $model->delivery_fee,
                                                2,
                                              ) ?></td>
                      </tr>
                    <?php endif; ?>
                    <?php if ($model->extra_charge > 0): ?>
                      <tr>
                        <td>Extra Charge</td>
                        <td class="text-end">$<?= number_format(
                                                $model->extra_charge,
                                                2,
                                              ) ?></td>
                      </tr>
                    <?php endif; ?>
                    <tr class="border-top border-top-dashed fs-15">
                      <th scope="row">Total Amount</th>
                      <th class="text-end text-success fs-5">$<?= number_format(
                                                                $model->grand_total,
                                                                2,
                                                              ) ?></th>
                    </tr>
                  </tbody>
                </table>
                <!--end table-->
              </div>
              <?php if ($model->remark): ?>
                <div class="mt-4">
                  <div class="alert alert-info">
                    <p class="mb-0"><span class="fw-semibold">NOTES:</span>
                      <span><?= nl2br(
                              Html::encode($model->remark),
                            ) ?></span>
                    </p>
                  </div>
                </div>
              <?php endif; ?>

              <?php if ($outlet && $outlet->terms): ?>
                <div class="mt-4">
                  <h6 class="text-muted text-uppercase fw-semibold mb-2">Terms & Conditions:</h6>
                  <p class="text-muted mb-0">
                    <?= nl2br(
                      Html::encode($outlet->terms),
                    ) ?></p>
                </div>
              <?php endif; ?>
              <div class="row font-size-sm invoice-signature">
                <div class="col-3 offset-2 text-center">Customer / អ្នកទិញ</div>
                <div class="col-3 offset-2 text-center">Sales / អ្នកលក់</div>
              </div>
            </div>
            <!--end card-body-->
          </div><!--end col-->
        </div><!--end row-->
      </div>
      <!--end card-->
    </div>
    <!--end col-->
    <div class="col-xxl-3 mt-xxl-0 mt-4 d-print-none" data-html2canvas-ignore="true">
      <div class="card">
        <div class="card-body">
          <h6 class="text-muted text-uppercase fw-semibold mb-3">Margin Summary</h6>
          <div class="d-flex justify-content-between mb-2">
            <span>Total Cost</span>
            <strong>$<?= number_format($totalCost, 2) ?></strong>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span>Total Sale</span>
            <strong>$<?= number_format($totalSale, 2) ?></strong>
          </div>
          <hr />
          <div class="d-flex justify-content-between mb-2">
            <span>Margin</span>
            <strong>$<?= number_format($totalMargin, 2) ?></strong>
          </div>
          <div class="d-flex justify-content-between">
            <span>Margin %</span>
            <strong><?= number_format($marginPercent, 2) ?>%</strong>
          </div>
        </div>
      </div>

      <?php if (!empty($activities)): ?>
        <div class="card mt-3">
          <div class="card-body">
            <h6 class="text-muted text-uppercase fw-semibold mb-3">Activity Log</h6>
            <div style="max-height:300px; overflow:auto;">
              <?php foreach ($activities as $act): ?>
                <div class="mb-2">
                  <div>
                    <?= Html::encode($act->formatTimestamp()) ?> - <strong>
                      <?= Html::encode($act->action) ?></strong>
                    by <?= Html::encode(
                          $act->user ? $act->user->getName() : 'System',
                        ) ?></div>
                  <div class="text-muted">IP: <?= Html::encode(
                                                $act->ip_address,
                                              ) ?></div>
                </div>
                <hr class="my-2" />
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <!--end row-->
</div>

<?php
// Register JsBarcode library
$this->registerJsFile('https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

// Register print styles
$printCss = <<<CSS
@media print {
  body {
    font-size: 11px;
  }
  
  .quotation-view {
    font-size: 11px;
  }
  
  h1, h2, h3, h4, h5, h6 {
    font-size: 12px;
  }
  
  .quotation-title {
    font-size: 18px !important;
  }
  
  .card {
    border: none;
    page-break-inside: avoid;
  }
  
  .table {
    font-size: 10px;
  }
  
  .table th, .table td {
    padding: 0.25rem !important;
  }
  
  .card-header, .card-body {
    padding: 0.75rem !important;
  }
  
  .btn, .btn-group {
    display: none !important;
  }
  
  .d-print-none {
    display: none !important;
  }
  
  p {
    margin-bottom: 0.25rem;
  }
}
CSS;
$this->registerCss($printCss);

$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$js = <<<JS
function submitPost(url) {
  var form = document.createElement('form');
  form.method = 'POST';
  form.action = url;

  var csrfInput = document.createElement('input');
  csrfInput.type = 'hidden';
  csrfInput.name = '{$csrfParam}';
  csrfInput.value = '{$csrfToken}';
  form.appendChild(csrfInput);

  document.body.appendChild(form);
  form.submit();
}

$(document).on('click', '.quotation-action', function (e) {
  e.preventDefault();
  var url = $(this).data('action-url');
  var title = $(this).data('action-title');
  var text = $(this).data('action-text');

  Swal.fire({
    title: title,
    text: text,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, continue',
    cancelButtonText: 'No, keep it'
  }).then(function(result) {
    if (result.isConfirmed) {
      submitPost(url);
    }
  });
});

// Print Quotation using current view
$(document).ready(function() {
  $('#btn-print-quotation').on('click', function () {
    window.print();
  });
});

// Generate barcode
$(document).ready(function() {
  if (typeof JsBarcode === 'undefined') {
    console.warn('JsBarcode library not loaded');
    return;
  }
  
  var quotationCode = "$model->code";
  if (quotationCode && quotationCode.trim()) {
    try {
      JsBarcode('#quotation-barcode', quotationCode, {
        format: 'CODE128',
        width: 1,
        height: 20,
        displayValue: false,
        margin: 1
      });
    } catch(e) {
      console.error('Barcode generation error:', e);
    }
  }
});
JS;
$this->registerJs($js);
?>