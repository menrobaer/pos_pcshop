<?php

use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
  'action' => ['index'],
  'method' => 'get',
  'options' => [
    'class' => 'd-flex gap-3 flex-wrap align-items-center',
    'data-pjax' => 1,
  ],
]);
?>

<div id="filterContainer" class="d-flex gap-3 flex-wrap">
  <?= $form
    ->field($model, 'status', [
      'options' => ['class' => 'd-flex flex-column filter-field'],
    ])
    ->dropDownList($model->getStatusList(), [
      'prompt' => 'All',
      'class' => 'form-select has-select2',
      'onchange' => '$(this.form).trigger("submit")',
    ])
    ->label('Status') ?>
</div>
<div class="d-flex flex-column">
  <label class="control-label" for="searchCustomerList">Search</label>
  <div class="search-box">
    <?= $form
      ->field($model, 'name', [
        'template' => '{input}',
        'options' => ['tag' => false],
      ])
      ->textInput([
        'class' => 'form-control ps-5',
        'placeholder' => 'Search Customers...',
        'id' => 'searchCustomerList',
      ])
      ->label(false) ?>
    <i class="ri-search-line search-icon"></i>
  </div>
</div>
<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
$(document).on('keyup', '#searchCustomerList', function() {
  var form = $(this).closest('form');
  clearTimeout(window.searchCustomerTimeout);
  window.searchCustomerTimeout = setTimeout(function() {
    form.trigger('submit');
  }, 500);
});
$(document).on('focus click', '#searchCustomerList', function() {
  $(this).select();
});
JS;
$this->registerJs($js);
?>