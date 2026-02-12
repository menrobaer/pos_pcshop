<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\SupplierSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Suppliers';
$this->params['breadcrumbs'][] = $this->title;

echo \app\widgets\Modal::widget([
  'id' => 'modal-supplier',
  'size' => 'modal-md',
]);
?>
<div class="supplier-index">
  <?php Pjax::begin(['id' => 'supplier-pjax-container']); ?>
  <div class="card">
    <div class="card-body">
      <div class="card-header border-0">
        <div class="row g-4">
          <div class="col-sm-auto">
            <div>
              <?= Html::button(
                '<i class="ri-add-line align-bottom me-1"></i> Add Supplier',
                [
                  'class' => 'btn btn-success',
                  'data' => [
                    'bs-toggle' => 'modal',
                    'bs-target' => '#modal-supplier',
                    'title' => 'Add New Supplier',
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
              ]) ?>
            </div>
          </div>
        </div>
      </div>      
        <?= GridView::widget([
          'dataProvider' => $dataProvider,
          'tableOptions' => [
            'id' => 'table-supplier-list',
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
            'name',
            'phone',
            'address',
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
                      'bs-target' => '#modal-supplier',
                      'title' => 'Update Supplier : ' . $model->name,
                      'url' => Url::to(['update', 'id' => $model->id]),
                    ],
                  ]);
                },
                'delete' => function ($url, $model) {
                   if ($model->isUsed()) {
                  return Html::a(
                    '<i class="ri ri-delete-bin-2-line"></i>',
                    '#',
                    [
                      'title' => 'Delete',
                      'class' =>
                        'btn btn-sm btn-outline-danger disabled',
                      'data-bs-toggle' => 'tooltip',
                      'data-bs-placement' => 'top',
                      'data-bs-title' =>
                        'This customer cannot be deleted because it is used in transactions.',
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
                      'data-pjax-container' => '#supplier-pjax-container',
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
