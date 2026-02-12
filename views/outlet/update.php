<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Outlet $model */

$this->title = 'Outlet Settings';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="outlet-update">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1"><?= Html::encode(
                      $this->title,
                    ) ?></h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                      'options' => ['enctype' => 'multipart/form-data'],
                    ]); ?>

                    <div class="row">
                        <div class="col-md-4">
                            <?= $form
                              ->field($model, 'imageFile')
                              ->widget(\app\widgets\ImageUploadWidget::class, [
                                'imageUrl' =>
                                  $model->isNewRecord ||
                                  empty($model->getImagePath())
                                    ? null
                                    : $model->getImagePath(),
                                'placeholder' => 'Upload Logo',
                              ])
                              ->label(false) ?>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <?= $form
                                      ->field($model, 'name')
                                      ->textInput(['maxlength' => true]) ?>
                                </div>
                                <div class="col-md-6">
                                    <?= $form
                                      ->field($model, 'phone')
                                      ->textInput(['maxlength' => true]) ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <?= $form
                                      ->field($model, 'email')
                                      ->textInput(['maxlength' => true]) ?>
                                </div>
                                <div class="col-md-6">
                                    <?= $form
                                      ->field($model, 'website')
                                      ->textInput(['maxlength' => true]) ?>
                                </div>
                            </div>
                            <?= $form->field($model, 'address')->textarea([
                              'rows' => 4,
                              'class' => 'form-control auto-height',
                            ]) ?>

                            <?= $form->field($model, 'terms')->textarea([
                              'rows' => 4,
                              'class' => 'form-control auto-height',
                            ]) ?>

                            <div class="form-group mt-3">
                              <?= Html::submitButton('Save Settings', [
                                'class' => 'btn btn-success',
                              ]) ?>
                          </div>
                        </div>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->registerJs(
  <<<JS
  function initAutoHeight() {
      $('.auto-height').on('input', function() {
          this.style.height = 'auto';
          this.style.height = Math.max(this.scrollHeight, 100) + 'px'; // 100px is approx 4 rows
      }).trigger('input');
  }
  initAutoHeight();
  JS
  ,
);
?>
