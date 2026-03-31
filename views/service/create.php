<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Service $model */

$this->title = 'Create Service';
$this->params['breadcrumbs'][] = ['label' => 'Services', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="service-create">
  <div class="card">
    <div class="card-header border-0 align-items-center d-flex">
      <h4 class="card-title mb-0 flex-grow-1"><?= Html::encode($this->title) ?></h4>
    </div>
    <div class="card-body">
      <?= $this->render('_form', [
        'model' => $model,
        'customers' => $customers,
        'items' => $items ?? [],
      ]) ?>
    </div>
  </div>
</div>