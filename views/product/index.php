<?php

use app\models\Product;

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\search\ProductSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Products';
$this->params['breadcrumbs'][] = $this->title;

echo \app\widgets\Modal::widget([
  'id' => 'modal-product',
  'size' => 'modal-lg',
]);
?>
<div class="product-index">
  <?php Pjax::begin(['id' => 'product-pjax-container']); ?>
  <div class="card">
    <div class="card-body">
      <div class="card-header border-0">
        <div class="row g-4">
          <div class="col-sm-auto">
            <div>
              <?= Html::button(
                '<i class="ri-add-line align-bottom me-1"></i> Add Product',
                [
                  'class' => 'btn btn-success',
                  'data' => [
                    'bs-toggle' => 'modal',
                    'bs-target' => '#modal-product',
                    'title' => 'Add New Product',
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
                'categories' => $categories,
                'brands' => $brands,
                'models' => $models,
              ]) ?>
            </div>
          </div>
        </div>
      </div>

      <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => [
          'id' => 'table-product-list',
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
          [
            'attribute' => 'image',
            'format' => 'raw',
            'value' => function ($model) {
              /** @var Product $model */
              return Html::img($model->getImagePath(), [
                'class' => 'rounded avatar-sm',
              ]);
            },
            'headerOptions' => ['style' => 'width: 60px'],
          ],
          [
            'attribute' => 'category_id',
            'value' => function ($model) {
              return $model->category ? $model->category->name : '-';
            },
          ],
          [
            'attribute' => 'brand_id',
            'value' => function ($model) {
              return $model->brand ? $model->brand->name : '-';
            },
          ],
          [
            'attribute' => 'model_id',
            'value' => function ($model) {
              return $model->model ? $model->model->name : '-';
            },
          ],
          'name',
          'price',
          [
            'headerOptions' => ['class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
            'attribute' => 'available',
            'value' => function ($model) {
              return $model->available;
            },
          ],
          [
            'attribute' => 'status',
            'format' => 'raw',
            'value' => function ($model) {
              // Assuming your model method returns a badge
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
                return Html::button('<i class="ri ri-pencil-line"></i>', [
                  'class' => 'btn btn-sm btn-outline-primary me-1',
                  'data' => [
                    'bs-toggle' => 'modal',
                    'bs-target' => '#modal-product',
                    'title' => 'Update Item : ' . $model->name,
                    'url' => Url::to(['update', 'id' => $model->id]),
                  ],
                ]);
              },
              'delete' => function ($url, $model) {
                return Html::a(
                  '<i class="ri ri-delete-bin-2-line"></i>',
                  $url,
                  [
                    'title' => 'Delete',
                    'class' => 'btn btn-sm btn-outline-danger sa-delete',
                    'data-pjax' => '0',
                    'data-name' => $model->name,
                    'data-pjax-container' => '#product-pjax-container',
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
$(document).on('click', '#table-product-list tbody tr', function(e) {
    // Prevent click if clicking on button or action elements
    if ($(e.target).closest('button, .btn, a[class*="btn"]').length) {
        return;
    }

    // Get the view link from the first action button
    const viewLink = $(this).find('a[title="View"]').attr('href');
    if (viewLink) {
        window.location.href = viewLink;
    }
});

// Add cursor pointer to rows
$('#table-product-list tbody tr').css('cursor', 'pointer');

// Re-apply on pjax reload
$(document).on('pjax:end', function() {
    $('#table-product-list tbody tr').css('cursor', 'pointer');
});
JS;
$this->registerJs($js);
?>