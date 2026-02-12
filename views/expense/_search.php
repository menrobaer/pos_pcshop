<?php

use yii\widgets\ActiveForm;
/** @var yii\web\View $this */
/** @var app\models\ExpenseSearch $model */
/** @var array $suppliers */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="expense-search">

  <?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'options' => [
      'class' => 'd-flex gap-2',
      'data-pjax' => 1,
    ],
  ]); ?>

    <?= $form
      ->field($model, 'supplier_id', [
        'options' => ['class' => 'd-flex align-items-center gap-2'],
      ])
      ->dropDownList($suppliers, [
        'prompt' => 'All',
        'class' => 'form-select has-select2',
        'onchange' => '$(this.form).trigger("submit")',
      ])
      ->label('Supplier') ?>

    <?= $form
      ->field($model, 'status', [
        'options' => ['class' => 'd-flex align-items-center gap-2 ms-2'],
      ])
      ->dropDownList($model->getStatusList(), [
        'prompt' => 'All',
        'class' => 'form-select has-select2',
        'onchange' => '$(this.form).trigger("submit")',
      ])
      ->label('Status') ?>
      <div class="d-flex align-items-center gap-2 ms-auto">
          <label class="control-label" for="searchExpenseList">Search</label>
          <div class="search-box">
              <?= $form
                ->field($model, 'globalSearch', [
                  'template' => '{input}',
                  'options' => ['tag' => false],
                ])
                ->textInput([
                  'class' => 'form-control ps-5',
                  'placeholder' => 'Search Code...',
                  'id' => 'searchExpenseList',
                ])
                ->label(false) ?>
              <i class="ri-search-line search-icon"></i>
          </div>
      </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$js = <<<JS
$(document).on('keyup', '#searchExpenseList', function() {
  var form = $(this).closest('form');
  clearTimeout(window.searchExpenseTimeout);
  window.searchExpenseTimeout = setTimeout(function() {
    form.trigger('submit');
  }, 500);
});
JS;
$this->registerJs($js);


?>
