<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\website\ProductVariation $modelVariation */
/** @var array $sources */
/** @var array $warranties */
/** @var string $submitLabel */
/** @var int|null $descriptionSourceVariationId */

$form = ActiveForm::begin([
  'options' => ['enctype' => 'multipart/form-data'],
]);

echo $this->render('_variation_form', [
  'form' => $form,
  'modelVariation' => $modelVariation,
  'sources' => $sources,
  'warranties' => $warranties,
  'descriptionSourceVariationId' => $descriptionSourceVariationId ?? null,
]);
?>

<div class="d-flex justify-content-end gap-2 mt-3">
  <?= Html::button('Cancel', [
    'class' => 'btn btn-light',
    'id' => 'btn-dismiss-modal',
  ]) ?>
  <?= Html::submitButton($submitLabel ?? 'Save', [
    'class' => 'btn btn-primary',
  ]) ?>
</div>

<?php ActiveForm::end();
