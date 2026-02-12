<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Expense $model */
/** @var array $suppliers */

$this->title = 'Update Expense: ' . $model->code;
$this->params['breadcrumbs'][] = ['label' => 'Expenses', 'url' => ['index']];
$this->params['breadcrumbs'][] = [
  'label' => $model->code,
  'url' => ['view', 'id' => $model->id],
];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="expense-update">
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
            ]) ?>
        </div>
    </div>
</div>
