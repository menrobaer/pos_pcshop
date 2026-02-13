<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\ProductModel $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="product-model-form">

  <?php $form = ActiveForm::begin(); ?>

  <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

  <?= $form
    ->field($model, 'status', [
      'template' => '<label class="form-check-label" for="checkbox-model-status">Status</label>
    <div class="form-check form-switch">{input}<label class="form-check-label" for="checkbox-model-status">Active</label></div>{error}{hint}',
    ])
    ->checkbox(
      [
        'class' => 'form-check-input',
        'role' => 'switch',
        'id' => 'checkbox-model-status',
        'label' => false,
      ],
      false,
    ) ?>

  <div class="d-flex mt-4 gap-3">
    <?= Html::button('Cancel', [
      'class' => 'btn btn-light px-5 rounded-pill',
      'id' => 'btn-dismiss-modal',
    ]) ?>
    <?= Html::submitButton(
      $model->isNewRecord ? 'Save Model' : 'Save Changes',
      ['class' => 'btn btn-dark text-uppercase rounded-pill px-5'],
    ) ?>
  </div>

  <?php ActiveForm::end(); ?>

</div>
