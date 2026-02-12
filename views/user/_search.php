<?php

use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\search\UserSearch $searchModel */
/** @var yii\widgets\ActiveForm $form */

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
      'prompt' => 'All Status',
      'class' => 'form-select',
      'onchange' => '$(this.form).trigger("submit")',
    ],
  )
  ->label('Status') ?>

<div class="d-flex align-items-center gap-2 ms-2">
    <label class="control-label" for="searchUserList">Search</label>
    <div class="search-box">
        <?= $form
          ->field($searchModel, 'globalSearch', [
            'template' => '{input}',
            'options' => ['tag' => false],
          ])
          ->textInput([
            'class' => 'form-control ps-5',
            'placeholder' => 'Search Users...',
            'id' => 'searchUserList',
          ])
          ->label(false) ?>
        <i class="ri-search-line search-icon"></i>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
$(document).on('keyup', '#searchUserList', function() {
  var form = $(this).closest('form');
  clearTimeout(window.searchUserTimeout);
  window.searchUserTimeout = setTimeout(function() {
    form.trigger('submit');
  }, 500);
});
$(document).on('focus click', '#searchUserList', function() {
  $(this).select();
});
JS;
$this->registerJs($js);


?>
