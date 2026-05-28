<?php

use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
  'action' => ['index'],
  'method' => 'get',
  'options' => [
    'class' => 'd-flex gap-3 flex-wrap align-items-center',
    'data-pjax' => 1,
    'id' => 'product-search-form',
  ],
]);
?>
<div id="filterContainer" class="d-flex gap-3 flex-wrap">
  <?= $form
    ->field($searchModel, 'category_id', [
      'options' => ['class' => 'd-flex flex-column filter-field'],
    ])
    ->dropDownList($categories, [
      'prompt' => 'All',
      'class' => 'form-select has-select2',
      'onchange' => '$(this.form).trigger("submit")',
    ])
    ->label('Category') ?>
  <?= $form
    ->field($searchModel, 'brand_id', [
      'options' => ['class' => 'd-flex flex-column filter-field'],
    ])
    ->dropDownList($brands, [
      'prompt' => 'All',
      'class' => 'form-select has-select2',
      'onchange' => '$(this.form).trigger("submit")',
    ])
    ->label('Brand') ?>
  <?= $form
    ->field($searchModel, 'model_id', [
      'options' => ['class' => 'd-flex flex-column filter-field'],
    ])
    ->dropDownList($models, [
      'prompt' => 'All',
      'class' => 'form-select has-select2',
      'onchange' => '$(this.form).trigger("submit")',
    ])
    ->label('Model') ?>
  <?= $form
    ->field($searchModel, 'status', [
      'options' => ['class' => 'd-flex flex-column filter-field'],
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
</div>
<div class="d-flex flex-column">
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

  // Search field functionality
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