<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Quotation $model */
/** @var bool $isDuplicate */

$isDuplicate = $isDuplicate ?? false;
$this->title = $isDuplicate ? 'Duplicate Quotation' : 'Create Quotation';
$this->params['breadcrumbs'][] = ['label' => 'Quotations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="quotation-create">
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
              'isDuplicate' => $isDuplicate ?? false,
              'items' => $items ?? null,
            ]) ?>
        </div>
    </div>
</div>
