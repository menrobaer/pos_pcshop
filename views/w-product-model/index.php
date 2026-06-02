<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\ProductModelSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $brands */

$this->title = 'Product Models';
$this->params['breadcrumbs'][] = $this->title;

echo \app\widgets\Modal::widget([
  'id' => 'modal-product-model',
  'size' => 'modal-md',
]);

$this->registerJsFile('https://code.jquery.com/ui/1.13.1/jquery-ui.min.js', [
  'depends' => [\yii\web\JqueryAsset::class],
]);

$isBrandFiltered = !empty($searchModel->brand_id);
?>
<div class="product-model-index">
  <style>
    #table-product-model-list tbody tr {
      cursor: <?= $isBrandFiltered ? 'move' : 'default' ?>;
    }

    .drop-placeholder {
      border: 1px dashed #adb5bd;
      background: #f8f9fa;
      height: 44px;
    }
  </style>
  <?php Pjax::begin(['id' => 'product-model-pjax-container']); ?>
  <div class="card">
    <div class="card-body">
      <div class="card-header border-0">
        <div class="row g-4">
          <div class="col-sm-auto">
            <div class="d-flex gap-2">
              <?= Html::button(
                '<i class="ri-add-line align-bottom me-1"></i> Add Model',
                [
                  'class' => 'btn btn-success',
                  'data' => [
                    'bs-toggle' => 'modal',
                    'bs-target' => '#modal-product-model',
                    'title' => 'Add New Model',
                    'url' => Url::to(['create']),
                  ],
                ],
              ) ?>
            </div>
          </div>
          <div class="col-sm">
            <div class="d-flex justify-content-sm-end">
              <?= $this->render('_search', [
                'searchModel' => $searchModel,
                'brands' => $brands,
              ]) ?>
            </div>
          </div>
        </div>
      </div>
      <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => [
          'id' => 'table-product-model-list',
          'class' =>
          'table table-hover table-striped align-middle table-nowrap mb-0',
        ],
        'rowOptions' => function ($model) {
          return [
            'data-id' => $model->id,
            'data-brand-id' => $model->brand_id,
          ];
        },
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
          [
            'header' => '',
            'format' => 'raw',
            'contentOptions' => ['style' => 'width: 40px'],
            'value' => function () use ($isBrandFiltered) {
              if (!$isBrandFiltered) {
                return '<i class="ri-drag-move-2-fill text-muted opacity-50" title="Filter by brand to enable sorting"></i>';
              }
              return '<i class="ri-drag-move-2-fill text-muted"></i>';
            },
          ],
          ['class' => 'yii\grid\SerialColumn'],
          'name',
          [
            'attribute' => 'brand_id',
            'label' => 'Brand',
            'value' => function ($model) {
              return $model->brand ? $model->brand->name : '-';
            },
          ],
          'sort',
          [
            'attribute' => 'status',
            'format' => 'raw',
            'value' => function ($model) {
              return $model->getStatusBadge();
            },
          ],
          [
            'class' => 'yii\grid\ActionColumn',
            'header' => Yii::t('app', 'Actions'),
            'headerOptions' => [
              'class' => 'text-center',
              'style' => 'width: 120px',
            ],
            'contentOptions' => ['class' => 'text-center'],
            'template' => '{update} {delete}',
            'buttons' => [
              'update' => function ($url, $model) {
                return Html::button('<i class="ri ri-pencil-line"></i>', [
                  'class' => 'btn btn-sm btn-outline-primary me-1',
                  'data' => [
                    'bs-toggle' => 'modal',
                    'bs-target' => '#modal-product-model',
                    'title' => 'Update Model : ' . $model->name,
                    'url' => Url::to(['update', 'id' => $model->id]),
                  ],
                ]);
              },
              'delete' => function ($url, $model) {
                if ($model->isUsed()) {
                  return Html::button(
                    '<i class="ri ri-delete-bin-2-line"></i>',
                    [
                      'class' =>
                      'btn btn-sm btn-outline-danger me-1',
                      'title' => 'Delete',
                      'disabled' => true,
                      'data-pjax' => '0',
                      'data-name' => $model->name,
                      'data-pjax-container' =>
                      '#product-model-pjax-container',
                    ],
                  );
                }
                return Html::a(
                  '<i class="ri ri-delete-bin-2-line"></i>',
                  $url,
                  [
                    'title' => 'Delete',
                    'class' => 'btn btn-sm btn-outline-danger sa-delete',
                    'data-pjax' => '0',
                    'data-name' => $model->name,
                    'data-pjax-container' => '#product-model-pjax-container',
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
$dependentUrl = Url::to(['dependent']);

$script = <<<JS
function initProductModelSortable() {
  var selectedBrand = $('#productmodelsearch-brand_id').val();
  var sortableEnabled = selectedBrand !== '' && selectedBrand !== null && typeof selectedBrand !== 'undefined';
  var tableBody = $('#table-product-model-list tbody');
  if (!tableBody.length || typeof tableBody.sortable !== 'function') {
    return;
  }

  if (tableBody.data('ui-sortable')) {
    tableBody.sortable('destroy');
  }

  var hasNullBrand = false;
  $('#table-product-model-list tbody tr[data-id]').each(function () {
    var rowBrandId = $(this).attr('data-brand-id');
    if (rowBrandId === '' || rowBrandId === null || typeof rowBrandId === 'undefined') {
      hasNullBrand = true;
      return false;
    }
  });

  if (hasNullBrand) {
    $('#table-product-model-list tbody tr').css('cursor', 'default');
  }

  if (!sortableEnabled || hasNullBrand) {
    return;
  }

  $('#table-product-model-list tbody tr').css('cursor', 'move');

  tableBody.sortable({
    placeholder: 'drop-placeholder',
    helper: function (e, tr) {
      var originalCells = tr.children();
      var helper = tr.clone();
      helper.children().each(function (index) {
        $(this).width(originalCells.eq(index).width());
      });
      return helper;
    },
    stop: function () {
      var orderArr = [];
      $('#table-product-model-list tbody tr[data-id]').each(function () {
        orderArr.push($(this).data('id'));
      });

      orderArr = $.grep(orderArr, function (value) {
        return value !== '' && value !== null;
      });

      if (!orderArr.length) {
        return;
      }

      $.ajax({
        url: '$dependentUrl',
        type: 'post',
        dataType: 'json',
        data: {
          action: 'update_order',
          brand_id: selectedBrand,
          orderArr: orderArr
        },
        success: function (response) {
          if (response && response.status === 'saved') {
            $.pjax.reload({container: '#product-model-pjax-container', async: false});
          }
        }
      });
    }
  });

  tableBody.disableSelection();
}

initProductModelSortable();

$(document).on('pjax:end', '#product-model-pjax-container', function () {
  initProductModelSortable();
});
JS;

$this->registerJs($script);
?>