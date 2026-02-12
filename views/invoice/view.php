<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Invoice $model */
/** @var app\models\InvoicePayment $paymentModel */
/** @var app\models\InvoicePayment[] $payments */
/** @var array $paymentMethods */

$this->title = 'Invoice Details: ' . $model->code;
$this->params['breadcrumbs'][] = ['label' => 'Invoices', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$totalCost = 0.0;
foreach ($model->items as $it) {
  $costPer = $it->cost ?? 0.0;
  $qty = isset($it->quantity) ? (float) $it->quantity : 0.0;
  $totalCost += $costPer * $qty;
}
$totalSale = (float) $model->grand_total;
$totalMargin = $totalSale - $totalCost;
$marginPercent = $totalSale > 0 ? ($totalMargin / $totalSale) * 100 : 0;

/** @var app\components\Utils $utils */
$utils = Yii::$app->utils;
?>
<div class="invoice-view">
  <div class="row">
    <div class="col-xxl-9">
      <div class="card" id="demo">
        <div class="row">
          <div class="col-lg-12">
            <div class="card-header border-bottom-dashed p-4">
              <div class="d-flex">
                <div class="flex-grow-1">
                  <div class="mb-3">
                    <?php if ($outlet && $outlet->image): ?>
                      <?= Html::img($outlet->getImagePath(), [
                        'style' => 'max-height: 100px;',
                        'alt' => 'Logo',
                        'class' => 'mb-2',
                      ]) ?>
                    <?php endif; ?>
                    <h3 class="fw-bold mb-0">វិក្កយបត្រ - Invoice</h3>
                  </div>
                  <div class="mt-sm-5 mt-4">
                    <h6 class="text-muted text-uppercase fw-semibold">From Address</h6>
                    <p class="text-muted mb-1 fw-bold"><?= $outlet
                      ? Html::encode($outlet->name)
                      : 'POS VLC Company' ?></p>
                    <p class="text-muted mb-1"><?= $outlet
                      ? nl2br(Html::encode($outlet->address))
                      : '123 Business Avenue, Suite 456' ?></p>
                    <p class="text-muted mb-0"><span>Phone:</span> <?= $outlet
                      ? Html::encode($outlet->phone)
                      : '+(01) 234 6789' ?></p>
                  </div>
                </div>
                <div class="flex-shrink-0 mt-sm-0 mt-3 text-end">
                  <div class="mb-4 d-print-none" data-html2canvas-ignore="true">
                    <?php if (
                      $model->status != \app\models\Invoice::STATUS_CANCELLED &&
                      $model->status != \app\models\Invoice::STATUS_PAID
                    ): ?>
                      <?= Html::a(
                        '<i class="ri-edit-line align-bottom me-1"></i> Update',
                        ['update', 'id' => $model->id],
                        [
                          'class' => 'btn btn-primary btn-sm',
                        ],
                      ) ?>
                  <?php else: ?>
                      <?= Html::button(
                        '<i class="ri-edit-line align-bottom me-1"></i> Update',
                        [
                          'class' => 'btn btn-primary btn-sm disabled',
                          'title' =>
                            'Invoice is not editable after cancel/paid.',
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
                              <a class="dropdown-item invoice-action" href="#" data-action-url="<?= Url::to(
                                ['invoice/duplicate', 'id' => $model->id],
                              ) ?>" data-action-title="Duplicate Invoice" data-action-text="This will create a new invoice draft with the same items. Continue?">Duplicate</a>
                          </li>
                          <li>
                              <a class="dropdown-item invoice-action" href="#" data-action-url="<?= Url::to(
                                ['invoice/cancel', 'id' => $model->id],
                              ) ?>" data-action-title="Cancel Invoice" data-action-text="This will cancel the invoice and prevent further changes. Continue?">Cancel</a>
                          </li>
                      </ul>
                  </div>
                    <a href="javascript:window.print()" class="btn btn-soft-info btn-sm"><i class="ri-printer-line align-bottom me-1"></i> Print</a>
                  </div>
                  <h6><span class="text-muted fw-normal">Email:</span> <span><?= $outlet
                    ? Html::encode($outlet->email)
                    : 'support@posvlc.com' ?></span></h6>
                  <h6><span class="text-muted fw-normal">Website:</span> <a href="<?= $outlet
                    ? Html::encode($outlet->website)
                    : '#' ?>" class="link-primary">
                    <?= $outlet
                      ? Html::encode($outlet->website)
                      : 'www.posvlc.com' ?></a></h6>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-12">
            <div class="card-body p-4">
              <div class="row g-3">
                <div class="col-lg-3 col-6">
                  <p class="text-muted mb-2 text-uppercase fw-semibold">Invoice No</p>
                  <h5 class="fs-14 mb-0">#<span id="invoice-no"><?= Html::encode(
                    $model->code,
                  ) ?></span></h5>
                </div>
                <div class="col-lg-3 col-6">
                  <p class="text-muted mb-2 text-uppercase fw-semibold">Date</p>
                  <h5 class="fs-14 mb-0"><span id="invoice-date"><?= date(
                    'd M, Y',
                    strtotime($model->date),
                  ) ?></span></h5>
                </div>
                <div class="col-lg-3 col-6">
                  <p class="text-muted mb-2 text-uppercase fw-semibold">Due Date</p>
                  <h5 class="fs-14 mb-0 text-danger"><?= date(
                    'd M, Y',
                    strtotime($model->due_date),
                  ) ?></h5>
                </div>
                <div class="col-lg-3 col-6">
                  <p class="text-muted mb-2 text-uppercase fw-semibold">Total Amount</p>
                  <h5 class="fs-14 mb-0">$<span id="total-amount"><?= number_format(
                    $model->grand_total,
                    2,
                  ) ?></span></h5>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-12">
            <div class="card-body p-4 border-top border-top-dashed">
              <div class="row g-3">
                <div class="col-6">
                  <h6 class="text-muted text-uppercase fw-semibold mb-3">Customer Details</h6>
                  <p class="fw-bold mb-2 fs-15"><?= $model->customer
                    ? Html::encode($model->customer->name)
                    : '-' ?></p>
                  <p class="text-muted mb-1"><?= $model->customer
                    ? Html::encode($model->customer->address)
                    : '-' ?></p>
                  <p class="text-muted mb-0"><span>Phone: </span><?= $model->customer
                    ? Html::encode($model->customer->phone)
                    : '-' ?></p>
                </div>
                <div class="col-6 text-end">
                  <h6 class="text-muted text-uppercase fw-semibold mb-3">Invoice Summary</h6>
                  <p class="mb-1"><span class="text-muted">Paid:</span> $<?= number_format(
                    $model->paid_amount,
                    2,
                  ) ?></p>
                  <p class="mb-0"><span class="text-muted">Balance:</span> $<?= number_format(
                    $model->balance_amount,
                    2,
                  ) ?></p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-12">
            <div class="card-body p-4">
              <div class="table-responsive">
                <table class="table table-borderless text-center align-top mb-0">
                  <thead>
                    <tr class="table-active">
                      <th scope="col" style="width: 50px;">#</th>
                      <th scope="col" class="text-start" style="min-width: 280px;">Product Details</th>
                      <th scope="col" style="min-width: 120px;">Serial</th>
                      <th scope="col" style="min-width: 80px;">Price</th>
                      <th scope="col" style="min-width: 80px;">Quantity</th>
                      <th scope="col" class="text-end" style="min-width: 80px;">Amount</th>
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
                      <td class="text-start">
                        <span class="fw-bold"><?= Html::encode(
                          $item->product_name,
                        ) ?></span>
                        <div class="d-flex align-items-center mt-2">
                          <div class="flex-grow-1">
                            <?php if ($item->sku): ?>
                              <p class="text-muted mb-1"><span class="fw-medium">SKU:</span> <?= Html::encode(
                                $item->sku,
                              ) ?></p>
                            <?php endif; ?>
                            <?php if ($item->description): ?>
                              <p class="text-muted mb-0"><?= nl2br(
                                Html::encode($item->description),
                              ) ?></p>
                            <?php endif; ?>
                          </div>
                        </div>
                      </td>
                      <td><?= Html::encode($item->serial ?: '-') ?></td>
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
                </table>
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
                    <?php if ($model->discount_amount > 0): ?>
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
                    <tr>
                      <td>Paid</td>
                      <td class="text-end">$<?= number_format(
                        $model->paid_amount,
                        2,
                      ) ?></td>
                    </tr>
                    <tr>
                      <td>Balance</td>
                      <td class="text-end text-danger">$<?= number_format(
                        $model->balance_amount,
                        2,
                      ) ?></td>
                    </tr>
                    <tr class="border-top border-top-dashed fs-15">
                      <th scope="row">Total Amount</th>
                      <th class="text-end text-success fs-5">$<?= number_format(
                        $model->grand_total,
                        2,
                      ) ?></th>
                    </tr>
                  </tbody>
                </table>
              </div>
              <?php if ($model->remark): ?>
              <div class="mt-4">
                <div class="alert alert-info">
                  <p class="mb-0"><span class="fw-semibold">NOTES:</span>
                    <span><?= nl2br(Html::encode($model->remark)) ?></span>
                  </p>
                </div>
              </div>
              <?php endif; ?>

              <?php if ($outlet && $outlet->terms): ?>
              <div class="mt-4">
                <h6 class="text-muted text-uppercase fw-semibold mb-2">Terms & Conditions:</h6>
                <p class="text-muted mb-0"><?= nl2br(
                  Html::encode($outlet->terms),
                ) ?></p>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xxl-3 mt-xxl-0 mt-4">
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
          <hr/>
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
      <?php $canAcceptPayment =
        $model->balance_amount > 0 &&
        $model->status != \app\models\Invoice::STATUS_CANCELLED; ?>
      <?php if ($canAcceptPayment): ?>
        <div class="card mt-3">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="text-muted text-uppercase fw-semibold mb-0">Record Payment</h6>
              <small class="text-muted">Balance: $<?= number_format(
                $model->balance_amount,
                2,
              ) ?></small>
            </div>
            <?php if ($paymentModel->hasErrors()): ?>
              <div class="alert alert-danger small mb-3">
                <?= Html::errorSummary($paymentModel, [
                  'header' => '',
                  'class' => 'mb-0',
                ]) ?>
              </div>
            <?php endif; ?>
            <?php if (empty($paymentMethods)): ?>
              <div class="alert alert-warning mb-0">
                Please add at least one payment method before recording payments.
              </div>
            <?php else: ?>
              <?= Html::beginForm(
                ['invoice/add-payment', 'id' => $model->id],
                'post',
                ['id' => 'invoice-payment-form'],
              ) ?>
                <div class="mb-3">
                  <label class="form-label d-flex justify-content-between">
                    <span>Amount</span>
                    <small class="text-muted">
                      Remaining: $<?= number_format(
                        $model->balance_amount,
                        2,
                      ) ?>
                    </small>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input
                      id="payment-amount-input"
                      type="number"
                      step="0.01"
                      min="0.01"
                      max="<?= number_format(
                        $model->balance_amount,
                        2,
                        '.',
                        '',
                      ) ?>"
                      name="InvoicePayment[amount]"
                      value="<?= number_format(
                        $paymentModel->amount,
                        2,
                        '.',
                        '',
                      ) ?>"
                      class="form-control"
                      required
                    >
                  </div>
                  <?= Html::error($paymentModel, 'amount', [
                    'class' => 'text-danger small',
                  ]) ?>
                  <div
                    id="payment-suggestions"
                    class="btn-group btn-group-sm mt-2"
                    role="group"
                    data-balance="<?= number_format(
                      $model->balance_amount,
                      2,
                      '.',
                      '',
                    ) ?>"
                  >
                    <button type="button" class="btn btn-outline-secondary payment-suggestion" data-percent="0.25">25%</button>
                    <button type="button" class="btn btn-outline-secondary payment-suggestion" data-percent="0.5">50%</button>
                    <button type="button" class="btn btn-outline-secondary payment-suggestion" data-percent="0.75">75%</button>
                    <button type="button" class="btn btn-outline-secondary payment-suggestion" data-percent="1">Full Amount</button>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label">Payment Method</label>
                  <?= Html::dropDownList(
                    'InvoicePayment[payment_method_id]',
                    $paymentModel->payment_method_id,
                    $paymentMethods,
                    [
                      'prompt' => 'Select payment method',
                      'class' => 'form-select',
                      'required' => true,
                    ],
                  ) ?>
                  <?= Html::error($paymentModel, 'payment_method_id', [
                    'class' => 'text-danger small',
                  ]) ?>
                </div>
                <div class="mb-3">
                  <label class="form-label">Payment Date</label>
                  <input
                    class="form-control"
                    type="text"
                    name="InvoicePayment[date]"
                    value="<?= Html::encode($paymentModel->date) ?>"
                    data-provider="flatpickr"
                    data-date-format="Y-m-d"
                    data-alt-input="true"
                    data-alt-format="d M, Y"
                    autocomplete="off"
                    required
                  >
                  <?= Html::error($paymentModel, 'date', [
                    'class' => 'text-danger small',
                  ]) ?>
                </div>
                <div class="mb-3">
                  <label class="form-label">Remark</label>
                  <textarea
                    name="InvoicePayment[remark]"
                    class="form-control"
                    rows="2"
                  ><?= Html::encode($paymentModel->remark) ?></textarea>
                </div>
                <div class="d-grid">
                  <button type="submit" class="btn btn-success">Record payment</button>
                </div>
              <?= Html::endForm() ?>
            <?php endif; ?>
          </div>
        </div>
      <?php elseif ($model->status == \app\models\Invoice::STATUS_CANCELLED): ?>
        <div class="card mt-3">
          <div class="card-body text-center text-danger">
            Payments are disabled for cancelled invoices.
          </div>
        </div>
      <?php else: ?>
        <div class="card mt-3">
          <div class="card-body text-center text-success">
            Invoice has already been settled.
          </div>
        </div>
      <?php endif; ?>
      <?php if (!empty($payments)): ?>
        <div class="card mt-3">
          <div class="card-body">
            <h6 class="text-muted text-uppercase fw-semibold mb-3">Payment History</h6>
            <div style="max-height: 260px; overflow: auto;">
              <table class="table table-borderless table-sm mb-0">
                <tbody>
                  <?php foreach ($payments as $payment): ?>
                    <tr class="border-bottom">
                      <td>
                        <div class="small">
                          <a href="<?= Url::to([
                            'invoice/view',
                            'id' => $model->id,
                          ]) ?>" class="text-decoration-underline"><?= Html::encode(
  $payment->code,
) ?></a>
                        </div>
                         <div>
                          <strong><?= Html::encode(
                            $utils->date($payment->date),
                          ) ?></strong>
                        </div>
                        </td>
                      <td class="text-end">
                        <div><?= Html::encode(
                          $payment->paymentMethod
                            ? $payment->paymentMethod->name
                            : '-',
                        ) ?></div>
                         <div class="text-primary fw-bold"><?= $utils->dollarFormat(
                           $payment->amount,
                         ) ?></div>
                        </td>
                    </tr>
                    <?php if ($payment->remark): ?>
                      <tr>
                        <td colspan="4" class="text-muted small">
                          <?= nl2br(Html::encode($payment->remark)) ?>
                        </td>
                      </tr>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      <?php endif; ?>
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
                <hr class="my-2"/>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>


<?php
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$balanceJs = json_encode($model->balance_amount);
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

  $(document).on('click', '.invoice-action', function (e) {
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

  var invoiceBalance = Number({$balanceJs} || 0);
  var paymentAmount = $('#payment-amount-input');
  var paymentForm = $('#invoice-payment-form');

  if (paymentAmount.length && paymentForm.length) {
    $('.payment-suggestion').on('click', function () {
      var percent = parseFloat($(this).data('percent'));
      if (isNaN(percent)) {
        return;
      }
      var suggested = invoiceBalance * percent;
      paymentAmount.val(suggested ? suggested.toFixed(2) : '0.00');
    });

    paymentForm.on('submit', function (e) {
      var value = Number(paymentAmount.val());
      if (isNaN(value) || value <= 0) {
        Swal.fire({
          icon: 'warning',
          title: 'Invalid amount',
          text: 'Please enter a positive payment amount.',
        });
        e.preventDefault();
        return false;
      }
      if (value > invoiceBalance) {
        Swal.fire({
          icon: 'warning',
          title: 'Amount exceeds balance',
          text: 'You cannot pay more than the remaining balance.',
        });
        e.preventDefault();
        return false;
      }
      return true;
    });
  }
JS;
$this->registerJs($js);


?>
