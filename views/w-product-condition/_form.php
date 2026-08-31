<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\ProductCondition $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="product-condition-form">

  <?php $form = ActiveForm::begin(); ?>

  <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

  <?= $form->field($model, 'color')->textInput([
    'type' => 'color',
    'value' => $model->color ?: '#ffffff',
  ]) ?>

  <?= $form->field($model, 'background_color')->textInput([
    'type' => 'color',
    'value' => $model->background_color ?: '#0ab39c',
  ]) ?>

  <?= $form
    ->field($model, 'status', [
      'template' => '<label class="form-check-label" for="checkbox-condition-status">Status</label>
    <div class="form-check form-switch">{input}<label class="form-check-label" for="checkbox-condition-status">Active</label></div>{error}{hint}',
    ])
    ->checkbox(
      [
        'class' => 'form-check-input',
        'role' => 'switch',
        'id' => 'checkbox-condition-status',
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
      $model->isNewRecord ? 'Save Condition' : 'Save Changes',
      ['class' => 'btn btn-dark text-uppercase rounded-pill px-5'],
    ) ?>
  </div>

  <?php ActiveForm::end(); ?>

</div>
