<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\PurchaseOrder $model */
/** @var bool $isDuplicate */

$isDuplicate = $isDuplicate ?? false;
$this->title = $isDuplicate
  ? 'Duplicate Purchase Order'
  : 'Create Purchase Order';
$this->params['breadcrumbs'][] = [
  'label' => 'Purchase Orders',
  'url' => ['index'],
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-order-create">
    <div class="card">
        <div class="card-header border-0 align-items-center d-flex">
            <h4 class="card-title mb-0 flex-grow-1"><?= Html::encode(
              $this->title,
            ) ?></h4>
        </div>
        <div class="card-body">
            <?= $this->render('_form', [
              'model' => $model,
              'suppliers' => $suppliers,
              'products' => $products,
              'isDuplicate' => $isDuplicate ?? false,
              'items' => $items ?? null,
            ]) ?>
        </div>
    </div>
</div>
