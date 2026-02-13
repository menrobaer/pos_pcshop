<?php

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
  ->field($searchModel, 'status', [
    'options' => ['class' => 'd-flex align-items-center gap-2'],
  ])
  ->dropDownList(
    [1 => 'Active', 0 => 'Inactive'],
    [
      'prompt' => 'All',
      'class' => 'form-select',
      'onchange' => '$(this.form).trigger("submit")',
    ],
  )
  ->label('Status') ?>
<div class="d-flex align-items-center gap-2 ms-2">
  <label class="control-label" for="searchBrandList">Search</label>
  <div class="search-box">
    <?= $form
      ->field($searchModel, 'name', [
        'template' => '{input}',
        'options' => ['tag' => false],
      ])
      ->textInput([
        'class' => 'form-control ps-5',
        'placeholder' => 'Search Brands...',
        'id' => 'searchBrandList',
      ])
      ->label(false) ?>
    <i class="ri-search-line search-icon"></i>
  </div>
</div>
<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
$(document).on('keyup', '#searchBrandList', function() {
  var form = $(this).closest('form');
  clearTimeout(window.searchBrandTimeout);
  window.searchBrandTimeout = setTimeout(function() {
    form.trigger('submit');
  }, 500);
});
$(document).on('focus click', '#searchBrandList', function() {
  $(this).select();
});
JS;
$this->registerJs($js);
?>