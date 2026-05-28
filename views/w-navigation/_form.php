<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;

?>

<div class="frm-navigation">

  <?php
  $validationUrl = ['navigation/validation'];
  if (!$model->isNewRecord) {
    $validationUrl['id'] = $model->id;
  }
  $form = ActiveForm::begin([
    'id' => 'frm-navigation',
    'enableAjaxValidation' => true,
    'enableClientValidation' => true,
    'validationUrl' => $validationUrl
  ]);
  ?>

  <?= $form->field($model, 'name')->textInput(['class' => 'form-control', 'autofocus' => true]) ?>
  <?= $form->field($model, 'slug')->textInput(['class' => 'form-control']) ?>

  <?= Html::submitButton('Save', ['class' => 'btn btn-warning btn-submit-base']) ?>

  <?php ActiveForm::end(); ?>
</div>

<?php

$script = <<<JS

JS;

$this->registerJs($script);
?>