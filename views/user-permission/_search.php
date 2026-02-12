<?php

use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\UserRoleSearch $searchModel */
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

<div class="d-flex align-items-center gap-2 ms-2">
    <label class="control-label" for="searchRoleList">Search</label>
    <div class="search-box">
        <?= $form
          ->field($searchModel, 'globalSearch', [
            'template' => '{input}',
            'options' => ['tag' => false],
          ])
          ->textInput([
            'class' => 'form-control ps-5',
            'placeholder' => 'Search Roles...',
            'id' => 'searchRoleList',
          ])
          ->label(false) ?>
        <i class="ri-search-line search-icon"></i>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
$(document).on('keyup', '#searchRoleList', function() {
  var form = $(this).closest('form');
  clearTimeout(window.searchRoleTimeout);
  window.searchRoleTimeout = setTimeout(function() {
    form.trigger('submit');
  }, 500);
});
$(document).on('focus click', '#searchRoleList', function() {
  $(this).select();
});
JS;
$this->registerJs($js);


?>
