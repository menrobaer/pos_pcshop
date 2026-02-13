<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\InvoiceSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $customers */

$this->title = 'Invoices';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="invoice-index">
  <?php Pjax::begin(['id' => 'invoice-pjax-container']); ?>
  <div class="card">
    <div class="card-body">
      <div class="card-header border-0">
        <div class="row g-4">
          <div class="col-sm-auto">
            <div>
              <?= Html::a(
                '<i class="ri-add-line align-bottom me-1"></i> Add Invoice',
                ['create'],
                [
                  'class' => 'btn btn-success',
                  'data-pjax' => '0',
                ],
              ) ?>
            </div>
          </div>
          <div class="col-sm">
            <div class="d-flex justify-content-sm-end">
              <?= $this->render('_search', [
                'model' => $searchModel,
                'customers' => $customers,
              ]) ?>
            </div>
          </div>
        </div>
      </div>

      <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => [
          'id' => 'table-invoice-list',
          'class' =>
          'table table-hover table-striped align-middle table-nowrap mb-0',
        ],
        'layout' => "
                        <div class='table-responsive'>
                            {items}
                        </div>
                        <hr>
                        <div class='row small text-muted'>
                            <div class='col-md-6'>
                                {summary}
                            </div>
                            <div class='col-md-6 text-end'>
                                {pager}
                            </div>
                        </div>
                    ",
        'pager' => [
          'class' => \yii\bootstrap5\LinkPager::class,
          'options' => ['class' => 'pagination justify-content-end'],
          'maxButtonCount' => 5,
          'linkOptions' => ['class' => 'page-link', 'data-pjax' => 1],
        ],
        'columns' => [
          ['class' => 'yii\grid\SerialColumn'],
          'code',
          [
            'attribute' => 'customer_id',
            'value' => function ($model) {
              return $model->customer ? $model->customer->name : '-';
            },
          ],
          [
            'attribute' => 'date',
            'value' => function ($model) {
              return \app\components\Utils::date($model->date);
            },
          ],
          [
            'attribute' => 'due_date',
            'value' => function ($model) {
              return \app\components\Utils::date($model->due_date);
            },
          ],
          [
            'attribute' => 'grand_total',
            'value' => function ($model) {
              return \app\components\Utils::dollarFormat(
                $model->grand_total,
              );
            },
          ],
          [
            'attribute' => 'balance_amount',
            'value' => function ($model) {
              return \app\components\Utils::dollarFormat(
                $model->balance_amount,
              );
            },
          ],
          [
            'attribute' => 'status',
            'format' => 'raw',
            'value' => function ($model) {
              return $model->getStatusBadge();
            },
          ],
          [
            'class' => 'yii\grid\ActionColumn',
            'header' => 'Actions',
            'headerOptions' => [
              'class' => 'text-center',
              'style' => 'width: 120px',
            ],
            'contentOptions' => ['class' => 'text-center'],
            'template' => '{view} {update} {delete}',
            'buttons' => [
              'view' => function ($url, $model) {
                return Html::a(
                  '<i class="ri ri-eye-line"></i>',
                  ['view', 'id' => $model->id],
                  [
                    'data-pjax' => '0',
                    'class' => 'btn btn-sm btn-outline-info me-1',
                    'title' => 'View',
                  ],
                );
              },
              'update' => function ($url, $model) {
                $isLocked =
                  $model->status ==
                  \app\models\Invoice::STATUS_CANCELLED ||
                  $model->status == \app\models\Invoice::STATUS_PAID;
                if ($isLocked) {
                  return Html::button(
                    '<i class="ri ri-pencil-line"></i>',
                    [
                      'class' =>
                      'btn btn-sm btn-outline-primary me-1 disabled',
                      'title' =>
                      'Invoice is not editable after cancel/paid.',
                    ],
                  );
                }
                return Html::a(
                  '<i class="ri ri-pencil-line"></i>',
                  ['update', 'id' => $model->id],
                  [
                    'data-pjax' => '0',
                    'class' => 'btn btn-sm btn-outline-primary me-1',
                    'title' => 'Update',
                  ],
                );
              },
              'delete' => function ($url, $model) {
                return Html::a(
                  '<i class="ri ri-delete-bin-2-line"></i>',
                  $url,
                  [
                    'title' => 'Delete',
                    'class' =>
                    'btn btn-sm btn-outline-danger sa-delete',
                    'data-pjax' => '0',
                    'data-name' => $model->code,
                    'data-pjax-container' =>
                    '#invoice-pjax-container',
                  ],
                );
              },
            ],
          ],
        ],
      ]) ?>
    </div>
  </div>
  <?php Pjax::end(); ?>
</div>

<?php
$js = <<<JS
$(document).on('click', '#table-invoice-list tbody tr', function(e) {
    if ($(e.target).closest('button, .btn, a[class*="btn"]').length) {
        return;
    }
    const viewLink = $(this).find('a[title="View"]').attr('href');
    if (viewLink) {
        window.location.href = viewLink;
    }
});
$('#table-invoice-list tbody tr').css('cursor', 'pointer');
$(document).on('pjax:end', function() {
    $('#table-invoice-list tbody tr').css('cursor', 'pointer');
});
JS;
$this->registerJs($js);
?>