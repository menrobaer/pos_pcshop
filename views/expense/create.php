<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Expense $model */
/** @var array $suppliers */
/** @var bool $isDuplicate */

$isDuplicate = $isDuplicate ?? false;
$this->title = $isDuplicate ? 'Duplicate Expense' : 'Create Expense';
$this->params['breadcrumbs'][] = ['label' => 'Expenses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="expense-create">
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
              'isDuplicate' => $isDuplicate ?? false,
              'items' => $items ?? null,
            ]) ?>
        </div>
    </div>
</div>
