<?php

use app\models\website\ProductVariation;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\website\Product $model */

$this->registerAssetBundle(\yii\web\YiiAsset::class);

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$variations = $model
  ->getVariations()
  ->andWhere(['or', ['status' => null], ['<>', 'status', ProductVariation::STATUS_DELETED]])
  ->orderBy(['id' => SORT_ASC])
  ->all();

echo \app\widgets\Modal::widget([
  'id' => 'modal-product',
  'size' => 'modal-lg',
]);
?>

<div class="product-view">
  <div class="card variation-panel">
    <div class="card-header variation-panel-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Product Variation</h5>
      <div class="d-flex gap-2">
        <?= Html::button(
            '<i class="ri-edit-line align-bottom me-1"></i> Update Product',
            [
              'class' => 'btn btn-primary',
              'data' => [
                'bs-toggle' => 'modal',
                'bs-target' => '#modal-product',
                'title' => 'Update Product : ' . $model->name,
                'url' => Url::to(['update', 'id' => $model->id]),
              ],
            ],
          ) ?>
        <?= Html::button(
            '<i class="ri-add-line align-bottom me-1"></i> Add Variation',
            [
              'class' => 'btn btn-success',
              'data' => [
                'bs-toggle' => 'modal',
                'bs-target' => '#modal-product',
                'title' => 'Add New Variation',
                'url' => Url::to(['create-variation', 'product_id' => $model->id]),
              ],
            ],
          ) ?>
      </div>
    </div>

    <div class="card-body">
      <?php if (empty($variations)): ?>
        <div class="text-center text-muted py-5">No variation found.</div>
      <?php else: ?>
        <div class="row g-4">
          <?php foreach ($variations as $variation): ?>
            <div class="col-xl-2 col-lg-2 col-md-6">
              <div class="variation-card-wrap h-100">
                <div class="variation-image-box position-relative">
                  <div class="variation-hover-actions">
                    <?= Html::button('<i class="ri-edit-line"></i>', [
                      'type' => 'button',
                      'class' => 'btn btn-sm btn-primary variation-hover-btn',
                      'title' => 'Edit Variation',
                      'data' => [
                        'bs-toggle' => 'modal',
                        'bs-target' => '#modal-product',
                        'title' => 'Update Variation : ' . ($variation->name ?: $model->name),
                        'url' => Url::to(['update-variation', 'id' => $variation->id]),
                      ],
                    ]) ?>
                    <?= Html::button('<i class="ri-file-copy-line"></i>', [
                      'type' => 'button',
                      'class' => 'btn btn-sm btn-warning variation-hover-btn',
                      'title' => 'Duplicate Variation',
                      'data' => [
                        'bs-toggle' => 'modal',
                        'bs-target' => '#modal-product',
                        'title' => 'Duplicate Variation : ' . ($variation->name ?: $model->name),
                        'url' => Url::to(['duplicate-variation', 'id' => $variation->id]),
                      ],
                    ]) ?>

                    <?= Html::a(
                        '<i class="ri ri-delete-bin-2-line"></i>',
                        [Url::to(['delete-variation', 'id' => $variation->id])],
                        [
                          'title' => 'Delete',
                          'class' => 'btn btn-sm btn-danger sa-delete variation-hover-btn',
                        ],
                      );?>
                  </div>

                  <?= Html::img($variation->getImagePath(), [
                    'class' => 'variation-image',
                    'alt' => $variation->name ?: $model->name,
                  ]) ?>
                </div>

                <div class="variation-content text-center mt-3">
                  <div class="variation-price fw-bold">$ <?= number_format((float) ($variation->price ?? 0), 2) ?></div>
                  <div class="variation-name fw-semibold"><?= Html::encode($variation->name ?: '-') ?></div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php
$css = <<<CSS
.variation-panel {
  border: 1px solid #d8dce4;
  border-radius: 8px;
  box-shadow: none;
}

.variation-panel-header {
  background: #fff;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #d8dce4;
}

.variation-card-wrap {
  display: flex;
  flex-direction: column;
}

.variation-image-box {
  min-height: 18rem;
  border: 1px solid #d7dbe4;
  border-radius: 8px;
  background: #fff;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 1.25rem 1rem 1rem;
}

.variation-hover-actions {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.625rem;
  opacity: 0;
  visibility: hidden;
  transform: translateY(8px);
  transition: all 0.2s ease;
  z-index: 5;
}

.variation-image-box:hover .variation-hover-actions {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.variation-hover-btn {
  width: 2.25rem;
  height: 2.25rem;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.variation-hover-btn i {
  line-height: 1;
}

.variation-image {
  width: 100%;
  max-width: 13.75rem;
  max-height: 13.75rem;
  object-fit: contain;
}

@media (max-width: 767.98px) {
  .variation-image-box {
    min-height: 14rem;
    padding-top: 1rem;
  }
}
CSS;

$this->registerCss($css);
?>