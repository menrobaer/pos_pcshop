<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Product $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="product-form">

  <?php $form = ActiveForm::begin([
    'options' => ['enctype' => 'multipart/form-data'],
  ]); ?>

  <div class="row">
    <div class="col-lg-4">
      <h6>Photo</h6>
        <div class="row">
          <div class="col-md-12">
            <?= $form
              ->field($model, 'imageFile')
              ->widget(\app\widgets\ImageUploadWidget::class, [
                'imageUrl' =>
                  $model->isNewRecord || empty($model->getImagePath())
                    ? null
                    : $model->getImagePath(),
              ])
              ->label(false) ?>
          </div>
        </div>
    </div>
    <div class="col-lg-8">
      <h6>Product Details</h6>
      <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

      <div class="row">
        <div class="col-lg-6">
          <?= $form
            ->field($model, 'sku')
            ->textInput(['readonly' => true, 'placeholder' => 'AUTO GENERATED']) ?>
        </div>
        <div class="col-lg-6">
          <?= $form->field($model, 'category_id')->dropDownList($categories, [
            'class' => 'has-select2',
            'prompt' => 'Select Category',
          ]) ?>
        </div>
        <div class="col-lg-6">
          <?= $form->field($model, 'brand_id')->dropDownList($brands, [
            'class' => 'has-select2',
            'prompt' => 'Select Brand',
          ]) ?>
        </div>
        <div class="col-lg-6">
          <?= $form->field($model, 'model_id')->dropDownList($models, [
            'class' => 'has-select2',
            'prompt' => 'Select Model',
          ]) ?>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-6">
          <?= $form
            ->field($model, 'cost')
            ->textInput(['type' => 'number', 'step' => '0.01']) ?>
        </div>
        <div class="col-lg-6">
          <?= $form
            ->field($model, 'price')
            ->textInput(['type' => 'number', 'step' => '0.01']) ?>
        </div>
      </div>
      <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

      <?= $form
        ->field($model, 'status', [
          'template' => '<label class="form-check-label" for="checkbox-status">Status</label>
        <div class="form-check form-switch">{input}<label class="form-check-label" for="checkbox-status">Active</label></div>{error}{hint}',
        ])
        ->checkbox(
          [
            'class' => 'form-check-input',
            'role' => 'switch',
            'id' => 'checkbox-status',
            'label' => false,
          ],
          false,
        ) ?>
    </div>
  </div>



  <div class="d-flex mt-4 gap-3">
    <?= Html::button('Cancel', [
      'class' => 'btn btn-light px-5 rounded-pill',
      'id' => 'btn-dismiss-modal',
    ]) ?>
    <?= Html::submitButton($model->isNewRecord ? 'Save Item' : 'Save Changes', [
      'class' => 'btn btn-dark text-uppercase rounded-pill px-5',
    ]) ?>
  </div>

  <?php ActiveForm::end(); ?>

</div>