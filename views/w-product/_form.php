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

  <h5>Product Details</h5>
  <div class="row">
    <div class="col-lg-4">
      <?= $form
        ->field($model, 'sku')
        ->textInput(['readonly' => true, 'placeholder' => 'AUTO GENERATED']) ?>
    </div>
    <div class="col-lg-8">
      <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    </div>
  </div>
  <div class="row">
    <div class="col-lg-4">
      <?= $form->field($model, 'condition_id')->dropDownList($conditions, [
        'class' => 'has-select2',
        'prompt' => 'Select Condition',
      ]) ?>
    </div>
    <div class="col-lg-8">
      <?= $form->field($model, 'slug')->textInput(['maxlength' => true]) ?>
    </div>
  </div>
  <div class="row">
    <div class="col-lg-4">
      <?= $form->field($model, 'category_id')->dropDownList($categories, [
        'class' => 'has-select2',
        'prompt' => 'Select Category',
      ]) ?>
    </div>
    <div class="col-lg-4">
      <?= $form->field($model, 'brand_id')->dropDownList($brands, [
        'class' => 'has-select2',
        'prompt' => 'Select Brand',
      ]) ?>
    </div>
    <div class="col-lg-4">
      <?= $form->field($model, 'model_id')->dropDownList($models, [
        'class' => 'has-select2',
        'prompt' => 'Select Model',
      ]) ?>
    </div>
  </div>

  <?php if($model->isNewRecord): ?>
  <h5>Product Variation</h5>
  <?= $this->render('_variation_form', [
    'form' => $form,
    'modelVariation' => $modelVariation,
    'sources' => $sources,
    'warranties' => $warranties,
  ]) ?>
  <?php endif; ?>
  
  <div class="my-2"></div>
  
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
    );
  ?>

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

<?php
$nameInputId = Html::getInputId($model, 'name');
$slugInputId = Html::getInputId($model, 'slug');

$js = <<<JS
(function () {
  var nameInput = document.getElementById('$nameInputId');
  var slugInput = document.getElementById('$slugInputId');

  if (!nameInput || !slugInput) {
    return;
  }

  function toSlug(value) {
    return (value || '')
      .toString()
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-');
  }

  var autoSync = !slugInput.value;

  function syncSlugFromName() {
    if (!autoSync) {
      return;
    }
    slugInput.value = toSlug(nameInput.value);
  }

  nameInput.addEventListener('input', syncSlugFromName);

  slugInput.addEventListener('input', function () {
    var generated = toSlug(nameInput.value);
    if (!slugInput.value) {
      autoSync = true;
      syncSlugFromName();
      return;
    }
    autoSync = slugInput.value === generated;
  });

  syncSlugFromName();
})();
JS;

$this->registerJs($js);
?>