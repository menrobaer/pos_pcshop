<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */

?>

<div class="product-brand-import">
  <?php $form = ActiveForm::begin([
    'action' => ['import-csv'],
    'method' => 'POST',
    'options' => ['enctype' => 'multipart/form-data'],
  ]); ?>
  <div class="mb-3">
    <label for="csv-file" class="form-label">Select CSV File</label>
    <input type="file" class="form-control" id="csv-file" name="csv_file" accept=".csv" required>
    <small class="text-muted">File should contain: name, description columns</small>
  </div>

  <div class="d-flex mt-4 gap-3">
    <?= Html::button('Cancel', [
      'class' => 'btn btn-light px-5 rounded-pill',
      'id' => 'btn-dismiss-modal',
    ]) ?>
    <?= Html::submitButton('Import', [
      'class' => 'btn btn-dark text-uppercase rounded-pill px-5',
    ]) ?>
  </div>
  <?php ActiveForm::end(); ?>
</div>