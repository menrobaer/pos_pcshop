<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\search\UserSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;

echo \app\widgets\Modal::widget([
  'id' => 'modal-user',
  'size' => 'modal-lg',
]);
?>
<div class="user-index">
    <?php Pjax::begin(['id' => 'user-pjax-container']); ?>
    <div class="card">
        <div class="card-body">
            <div class="card-header border-0">
                <div class="row g-4">
                    <div class="col-sm-auto">
                        <div>
                            <?= Html::button(
                              '<i class="ri-add-line align-bottom me-1"></i> Add User',
                              [
                                'class' => 'btn btn-success',
                                'data' => [
                                  'bs-toggle' => 'modal',
                                  'bs-target' => '#modal-user',
                                  'title' => 'Add New User',
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
                    'id' => 'table-user-list',
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
                        return Html::img($model->getImagePath(), [
                          'class' => 'rounded-circle avatar-xs',
                        ]);
                      },
                    ],
                    'first_name',
                    'last_name',
                    'username',
                    'email:email',
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
                      'template' => '{update}',
                      'buttons' => [
                        'update' => function ($url, $model) {
                          return Html::button(
                            '<i class="ri ri-pencil-line"></i>',
                            [
                              'class' => 'btn btn-sm btn-outline-primary me-1',
                              'data' => [
                                'bs-toggle' => 'modal',
                                'bs-target' => '#modal-user',
                                'title' => 'Update User : ' . $model->username,
                                'url' => Url::to([
                                  'update',
                                  'id' => $model->id,
                                ]),
                              ],
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
                              'data-name' => $model->username,
                              'data-pjax-container' => '#user-pjax-container',
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
