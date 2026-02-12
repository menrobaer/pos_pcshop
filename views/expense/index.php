<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\ExpenseSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $suppliers */

$this->title = 'Expenses';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="expense-index">
    <?php Pjax::begin(['id' => 'expense-pjax-container']); ?>
    <div class="card">
        <div class="card-body">
            <div class="card-header border-0">
                <div class="row g-4">
                    <div class="col-sm-auto">
                        <div>
                            <?= Html::a(
                              '<i class="ri-add-line align-bottom me-1"></i> Add Expense',
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
                              'suppliers' => $suppliers,
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
                <?= GridView::widget([
                  'dataProvider' => $dataProvider,
                  'tableOptions' => [
                    'id' => 'table-expense-list',
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
                      'attribute' => 'supplier_id',
                      'value' => function ($model) {
                        return $model->supplier ? $model->supplier->name : '-';
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
                              \app\models\Expense::STATUS_CANCELLED ||
                            $model->status == \app\models\Expense::STATUS_PAID;
                          if ($isLocked) {
                            return Html::button(
                              '<i class="ri ri-pencil-line"></i>',
                              [
                                'class' =>
                                  'btn btn-sm btn-outline-primary me-1 disabled',
                                'title' =>
                                  'Expense is not editable after cancel/paid.',
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
                                '#expense-pjax-container',
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
