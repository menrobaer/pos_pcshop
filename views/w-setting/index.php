<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\website\Setting $model */

$this->title = 'Site Setting';
$this->params['breadcrumbs'][] = ['label' => 'Site Setting', 'url' => ['index']];
?>

<div class="row justify-content-center">
	<div class="col-12 col-xl-10">
		<div class="card">
			<div class="card-header border-bottom-dashed">
				<h5 class="mb-0">Site Setting</h5>
			</div>
			<div class="card-body">
				<?php $form = ActiveForm::begin(); ?>

				<div class="row g-3">
					<div class="col-md-4">
						<?= $form->field($model, 'title')->textInput(['readonly' => true]) ?>
					</div>
					<div class="col-md-8">
						<?= $form->field($model, 'value')->textarea([
							'rows' => 3,
							'style' => 'font-family: monospace;',
						]) ?>
					</div>
				</div>

				<div class="d-flex justify-content-end mt-4">
					<?= Html::submitButton('Save Setting', [
						'class' => 'btn btn-primary px-5 rounded-pill',
					]) ?>
				</div>

				<?php ActiveForm::end(); ?>
			</div>
		</div>
	</div>
</div>
