<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Expense $model */
/** @var app\models\Outlet $outlet */
/** @var app\models\ActivityLog[] $activities */

$this->title = 'Expense Details: ' . $model->code;
$this->params['breadcrumbs'][] = [
  'label' => 'Expenses',
  'url' => ['index'],
];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="expense-view">
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
                    <h3 class="fw-bold mb-0">ចំណាយ - Expense</h3>
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
                      $model->status != \app\models\Expense::STATUS_CANCELLED &&
                      $model->status != \app\models\Expense::STATUS_PAID
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
                            'Expense is not editable after cancel/paid.',
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
                              <a class="dropdown-item expense-action" href="#" data-action-url="<?= Url::to(
                                ['expense/duplicate', 'id' => $model->id],
                              ) ?>" data-action-title="Duplicate Expense" data-action-text="This will create a new expense draft with the same items. Continue?">Duplicate</a>
                          </li>
                          <li>
                              <a class="dropdown-item expense-action" href="#" data-action-url="<?= Url::to(
                                ['expense/cancel', 'id' => $model->id],
                              ) ?>" data-action-title="Cancel Expense" data-action-text="This will cancel the expense and prevent further changes. Continue?">Cancel</a>
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
                  <p class="text-muted mb-2 text-uppercase fw-semibold">Expense No</p>
                  <h5 class="fs-14 mb-0">#<span id="expense-no"><?= Html::encode(
                    $model->code,
                  ) ?></span></h5>
                </div>
                <div class="col-lg-3 col-6">
                  <p class="text-muted mb-2 text-uppercase fw-semibold">Date</p>
                  <h5 class="fs-14 mb-0"><span id="expense-date"><?= date(
                    'd M, Y',
                    strtotime($model->date),
                  ) ?></span></h5>
                </div>
                <div class="col-lg-3 col-6">
                  <p class="text-muted mb-2 text-uppercase fw-semibold">Due Date</p>
                  <h5 class="fs-14 mb-0 text-danger"><span id="expense-due-date"><?= date(
                    'd M, Y',
                    strtotime($model->due_date),
                  ) ?></span></h5>
                </div>
                <div class="col-lg-3 col-6">
                  <p class="text-muted mb-2 text-uppercase fw-semibold">Status</p>
                  <h5 class="fs-14 mb-0"><?= $model->getStatusBadge() ?></h5>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-12">
            <div class="card-body p-4 border-top border-top-dashed">
              <div class="row g-3">
                <div class="col-6">
                  <h6 class="text-muted text-uppercase fw-semibold mb-3">Supplier Details</h6>
                  <p class="fw-bold mb-2 fs-15"><?= $model->supplier
                    ? Html::encode($model->supplier->name)
                    : '-' ?></p>
                  <p class="text-muted mb-1"><?= $model->supplier
                    ? Html::encode($model->supplier->address)
                    : '-' ?></p>
                  <p class="text-muted mb-0"><span>Phone: </span><?= $model->supplier
                    ? Html::encode($model->supplier->phone)
                    : '-' ?></p>
                </div>
                <div class="col-6 text-end">
                  <h6 class="text-muted text-uppercase fw-semibold mb-3">Expense Summary</h6>
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
                      <th scope="col" class="text-start" style="min-width: 280px;">Item Description</th>
                      <th scope="col" style="min-width: 80px;">Quantity</th>
                      <th scope="col" class="text-end" style="min-width: 100px;">Price</th>
                      <th scope="col" class="text-end" style="min-width: 100px;">Amount</th>
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
                          $item->title,
                        ) ?></span>
                        <div class="d-flex align-items-center mt-2">
                          <div class="flex-grow-1">
                            <?php if ($item->description): ?>
                              <p class="text-muted mb-0"><?= nl2br(
                                Html::encode($item->description),
                              ) ?></p>
                            <?php endif; ?>
                          </div>
                        </div>
                      </td>
                      <td><?= $item->quantity ?></td>
                      <td class="text-end">$<?= number_format(
                        $item->price,
                        2,
                      ) ?></td>
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
      <?php if (!empty($activities)): ?>
        <div class="card">
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

  $(document).on('click', '.expense-action', function (e) {
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
JS;
$this->registerJs($js);


?>
