<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Invoice $model */

$this->title = 'Update Invoice: ' . $model->code;
$this->params['breadcrumbs'][] = ['label' => 'Invoices', 'url' => ['index']];
$this->params['breadcrumbs'][] = [
  'label' => $model->code,
  'url' => ['view', 'id' => $model->id],
];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="sale-update">
    <div class="card">
        <div class="card-header border-0 align-items-center d-flex">
            <h4 class="card-title mb-0 flex-grow-1"><?= Html::encode(
              $this->title,
            ) ?></h4>
        </div>
        <div class="card-body">
            <?= $this->render('_form', [
              'model' => $model,
              'customers' => $customers,
              'products' => $products,
            ]) ?>
        </div>
    </div>
</div>
