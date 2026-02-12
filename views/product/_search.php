<?php

use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
  'action' => ['index'],
  'method' => 'get',
  'options' => [
    'class' => 'd-flex gap-2',
    'data-pjax' => 1,
  ],
]);
?>
<?= $form
  ->field($searchModel, 'category_id', [
    'options' => ['class' => 'd-flex align-items-center gap-2'],
  ])
  ->dropDownList($categories, [
    'prompt' => 'All',
    'class' => 'form-select has-select2',
    'onchange' => '$(this.form).trigger("submit")',
  ])
  ->label('Category') ?>
<?= $form
  ->field($searchModel, 'brand_id', [
    'options' => ['class' => 'd-flex align-items-center gap-2'],
  ])
  ->dropDownList($brands, [
    'prompt' => 'All',
    'class' => 'form-select has-select2',
    'onchange' => '$(this.form).trigger("submit")',
  ])
  ->label('Brand') ?>
<?= $form
  ->field($searchModel, 'status', [
    'options' => ['class' => 'd-flex align-items-center gap-2 ms-2'],
  ])
  ->dropDownList(
    [1 => 'Active', 0 => 'Inactive'],
    [
      'prompt' => 'All Status',
      'class' => 'form-select has-select2',
      'onchange' => '$(this.form).trigger("submit")',
    ],
  )
  ->label('Status') ?>
<div class="d-flex align-items-center gap-2 ms-2">
  <label class="control-label" for="searchProductList">Search</label>
  <div class="search-box">
    <?= $form
      ->field($searchModel, 'globalSearch', [
        'template' => '{input}',
        'options' => ['tag' => false],
      ])
      ->textInput([
        'class' => 'form-control ps-5',
        'placeholder' => 'Search Products...',
        'id' => 'searchProductList',
      ])
      ->label(false) ?>
    <i class="ri-search-line search-icon"></i>
  </div>
</div>
<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
$(document).on('keyup', '#searchProductList', function() {
  var form = $(this).closest('form');
  clearTimeout(window.searchTimeout);
  window.searchTimeout = setTimeout(function() {
    form.trigger('submit');
  }, 500);
});
$(document).on('focus click', '#searchProductList', function() {
  $(this).select();
});
JS;
$this->registerJs($js);
 ?>
