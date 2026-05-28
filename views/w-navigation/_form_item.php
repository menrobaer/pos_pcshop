<?php

use app\models\website\ProductBrand;
use app\models\website\ProductCategory;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\website\NavigationItem $model */

?>
<div class="frm-navigationItem">
  <?php

  $form = ActiveForm::begin([
    'id' => 'frm-navigationItem',
    'enableAjaxValidation' => false,
    'enableClientValidation' => true,
    'options' => ['enctype' => 'multipart/form-data'],
  ]);
  ?>

  <?= $form->field($model, 'name')->textInput(['class' => 'form-control', 'autofocus' => true]) ?>
  <?= $form->field($model, 'slug')->textInput(['class' => 'form-control']) ?>

  <?php
  $model->category_id = $model->isNewRecord ? [] : ArrayHelper::getColumn($model->data, 'category_id');
  echo $form->field($model, 'category_id')->dropDownList(
    ArrayHelper::map(ProductCategory::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name'),
    [
      'class' => 'has-select2',
      'multiple' => true,
      'prompt' => 'Select',
    ],
  )->label('Select Category');
  ?>
  <?php
  $model->brand_id = $model->isNewRecord ? [] : ArrayHelper::getColumn($model->data, 'brand_id');
  echo $form->field($model, 'brand_id')->dropDownList(
    ArrayHelper::map(ProductBrand::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name'),
    [
      'class' => 'has-select2',
      'multiple' => true,
      'prompt' => 'Select',
    ],
  )->label('Select Brand');
  ?>

  <div class="d-flex justify-content-end gap-2 mt-3">
    <?= Html::button('Cancel', [
      'class' => 'btn btn-light',
      'id' => 'btn-dismiss-modal',
    ]) ?>
    <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
  </div>

  <?php ActiveForm::end(); ?>
</div>
