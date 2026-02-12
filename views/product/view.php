<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use app\models\Inventory;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Product $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

// Check if product has inventory records
$inventoryCount = $model->getInventories()->count();
$isProductUsed = $inventoryCount > 0;

echo \app\widgets\Modal::widget([
  'id' => 'modal-product',
  'size' => 'modal-md',
]);
?>

<div class="product-view">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom-dashed d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h5 class="card-title mb-0 fw-bold"><?= Html::encode(
                          $this->title,
                        ) ?></h5>
                        <small class="text-muted">SKU: <span class="fw-semibold"><?= Html::encode(
                          $model->sku,
                        ) ?></span></small>
                    </div>
                    <div class="d-flex gap-2">
                        <?= Html::button(
                          '<i class="ri-edit-line me-1"></i> Update',
                          [
                            'class' => 'btn btn-primary btn-sm',
                            'data' => [
                              'bs-toggle' => 'modal',
                              'bs-target' => '#modal-product',
                              'title' => 'Update Item : ' . $model->name,
                              'url' => Url::to(['update', 'id' => $model->id]),
                            ],
                          ],
                        ) ?>
                        <?php if ($isProductUsed): ?>
                            <?= Html::button(
                              '<i class="ri-delete-bin-line me-1"></i> Delete',
                              [
                                'class' => 'btn btn-danger btn-sm disabled',
                                'title' =>
                                  'Product cannot be deleted because it has been used in ' .
                                  $inventoryCount .
                                  ' transaction(s)',
                                'data-bs-toggle' => 'tooltip',
                              ],
                            ) ?>
                        <?php else: ?>
                            <?= Html::a(
                              '<i class="ri-delete-bin-line me-1"></i> Delete',
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
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- Left: Image & Stock Summary -->
                        <div class="col-lg-3">
                            <!-- Product Image -->
                            <div class="text-center mb-4">
                                <?= Html::img($model->getImagePath(), [
                                  'class' =>
                                    'img-fluid rounded border border-light shadow-sm',
                                  'style' =>
                                    'max-height: 280px; object-fit: contain;',
                                  'alt' => $model->name,
                                ]) ?>
                                <div class="mt-3">
                                    <?= $model->getStatusBadge() ?>
                                </div>
                            </div>

                            <!-- Stock Summary Box -->
                            <div class="card bg-light-subtle border-0 mb-3">
                                <div class="card-body text-center">
                                    <h6 class="text-muted text-uppercase mb-2 fw-semibold">Current Stock</h6>
                                    <h2 class="fw-bold text-primary mb-0"><?= number_format(
                                      $model->available,
                                    ) ?></h2>
                                    <small class="text-muted">Units Available</small>
                                </div>
                            </div>

                            <!-- Stock Valuation Box -->
                            <div class="card bg-info-subtle border-0">
                                <div class="card-body text-center">
                                    <h6 class="text-muted text-uppercase mb-2 fw-semibold">Stock Value</h6>
                                    <h3 class="fw-bold text-info mb-0">
                                        <?= Yii::$app->formatter->asCurrency(
                                          $model->available * $model->price,
                                          'USD',
                                        ) ?>
                                    </h3>
                                    <small class="text-muted">@ Sale Price</small>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Product Details -->
                        <div class="col-lg-9">
                            <!-- Product Information Table -->
                            <div class="table-responsive">
                                <table class="table table-borderless table-sm">
                                    <tbody>
                                        <tr>
                                            <td class="fw-semibold text-muted" style="width: 30%;">Product Name</td>
                                            <td><?= Html::encode(
                                              $model->name,
                                            ) ?></td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <td class="fw-semibold text-muted">SKU</td>
                                            <td><?= Html::encode(
                                              $model->sku,
                                            ) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-muted">Category</td>
                                            <td><?= $model->category
                                              ? Html::encode(
                                                $model->category->name,
                                              )
                                              : '<span class="text-muted">(not set)</span>' ?></td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <td class="fw-semibold text-muted">Brand</td>
                                            <td><?= $model->brand
                                              ? Html::encode(
                                                $model->brand->name,
                                              )
                                              : '<span class="text-muted">(not set)</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-muted">Serial Number</td>
                                            <td><?= Html::encode(
                                              $model->serial,
                                            ) ?></td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <td class="fw-semibold text-muted">Description</td>
                                            <td><?= nl2br(
                                              Html::encode($model->description),
                                            ) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-muted">Cost Price</td>
                                            <td><span class="text-danger fw-bold"><?= Yii::$app->formatter->asCurrency(
                                              $model->cost,
                                              'USD',
                                            ) ?></span></td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <td class="fw-semibold text-muted">Sale Price</td>
                                            <td><span class="text-success fw-bold"><?= Yii::$app->formatter->asCurrency(
                                              $model->price,
                                              'USD',
                                            ) ?></span></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-muted">Margin</td>
                                            <td>
                                                <?php
                                                $margin =
                                                  $model->price - $model->cost;
                                                $marginPercent =
                                                  $model->cost > 0
                                                    ? ($margin / $model->cost) *
                                                      100
                                                    : 0;
                                                ?>
                                                <span class="fw-bold"><?= Yii::$app->formatter->asCurrency(
                                                  $margin,
                                                  'USD',
                                                ) ?></span>
                                                <span class="text-primary">(<?= number_format(
                                                  $marginPercent,
                                                  2,
                                                ) ?>%)</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom-dashed d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="ri-history-line me-2"></i> Stock Movement History
                    </h5>
                    <span class="badge bg-secondary">PO & Invoices</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <?= GridView::widget([
                          'dataProvider' => new \yii\data\ActiveDataProvider([
                            'query' => $model->getInventories(),
                            'pagination' => ['pageSize' => 15],
                          ]),
                          'tableOptions' => [
                            'class' => 'table table-hover align-middle mb-0',
                          ],
                          'layout' =>
                            "{items}\n<div class='d-flex justify-content-center p-3 border-top'>{pager}</div>",
                          'emptyText' =>
                            '<div class="p-5 text-center"><i class="ri-inbox-line display-5 text-muted"></i><p class="text-muted mt-2">No inventory transactions found.</p></div>',
                          'columns' => [
                            [
                              'attribute' => 'created_at',
                              'label' => 'Date & Time',
                              'value' => function ($inv) {
                                return Yii::$app->utils->dateTime(
                                  $inv->created_at,
                                );
                              },
                              'headerOptions' => [
                                'class' =>
                                  'bg-light fw-semibold text-uppercase text-muted small',
                              ],
                              'contentOptions' => ['class' => 'fw-500'],
                            ],
                            [
                              'attribute' => 'type',
                              'label' => 'Reference Document',
                              'format' => 'raw',
                              'headerOptions' => [
                                'class' =>
                                  'bg-light fw-semibold text-uppercase text-muted small',
                              ],
                              'value' => function ($inv) {
                                $label = $inv->typeLabel;
                                $route = '';
                                $icon = '';
                                $badgeClass = 'bg-light text-dark';

                                if (
                                  $inv->type === Inventory::TYPE_PURCHASE_ORDER
                                ) {
                                  $route = 'purchase-order/view';
                                  $icon =
                                    '<i class="ri-shopping-cart-line me-1"></i>';
                                  $badgeClass = 'bg-info-subtle text-info';
                                } elseif (
                                  $inv->type === Inventory::TYPE_INVOICE
                                ) {
                                  $route = 'invoice/view';
                                  $icon =
                                    '<i class="ri-file-text-line me-1"></i>';
                                  $badgeClass =
                                    'bg-success-subtle text-success';
                                }

                                $text = $label . ' #' . $inv->transaction_id;
                                if ($route) {
                                  return Html::a(
                                    $icon . $text,
                                    [$route, 'id' => $inv->transaction_id],
                                    [
                                      'class' =>
                                        'text-decoration-none fw-semibold link-primary',
                                      'data-pjax' => '0',
                                    ],
                                  );
                                }
                                return $text;
                              },
                            ],
                            [
                              'attribute' => 'in',
                              'label' => 'IN (+)',
                              'headerOptions' => [
                                'class' =>
                                  'bg-light fw-semibold text-uppercase text-muted small text-center',
                              ],
                              'contentOptions' => [
                                'class' => 'text-center fw-bold',
                              ],
                              'value' => function ($inv) {
                                return $inv->in > 0
                                  ? '<span class="text-success fs-6">+' .
                                      number_format($inv->in) .
                                      '</span>'
                                  : '<span class="text-muted">—</span>';
                              },
                              'format' => 'raw',
                            ],
                            [
                              'attribute' => 'out',
                              'label' => 'OUT (-)',
                              'headerOptions' => [
                                'class' =>
                                  'bg-light fw-semibold text-uppercase text-muted small text-center',
                              ],
                              'contentOptions' => [
                                'class' => 'text-center fw-bold',
                              ],
                              'value' => function ($inv) {
                                return $inv->out > 0
                                  ? '<span class="text-danger fs-6">-' .
                                      number_format($inv->out) .
                                      '</span>'
                                  : '<span class="text-muted">—</span>';
                              },
                              'format' => 'raw',
                            ],
                          ],
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>