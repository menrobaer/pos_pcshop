<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\User $model */

$this->title = 'My Profile';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row justify-content-center py-4">
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
          <div>
            <h5 class="card-title mb-0">Account Settings</h5>
            <small class="text-muted">Manage your personal information</small>
          </div>
        </div>
        <?php if (Yii::$app->session->hasFlash('success')): ?>
          <div class="alert alert-success mb-4">
            <?= Html::encode(Yii::$app->session->getFlash('success')) ?>
          </div>
        <?php endif; ?>
        <?php if (Yii::$app->session->hasFlash('error')): ?>
          <div class="alert alert-danger mb-4">
            <?= Html::encode(Yii::$app->session->getFlash('error')) ?>
          </div>
        <?php endif; ?>
        <?php $form = ActiveForm::begin([
          'id' => 'profile-form',
          'options' => ['enctype' => 'multipart/form-data'],
        ]); ?>
          <h6>Photo</h6>
          <div class="row">
            <div class="col-md-12">
              <?= $form
                ->field($model, 'imageFile')
                ->widget(\app\widgets\ImageUploadWidget::class, [
                  'imageUrl' =>
                    $model->isNewRecord || empty($model->getImagePath())
                      ? null
                      : $model->getImagePath(),
                ])
                ->label(false) ?>
            </div>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <?= $form
                ->field($model, 'first_name')
                ->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
              <?= $form
                ->field($model, 'last_name')
                ->textInput(['maxlength' => true]) ?>
            </div>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <?= $form
                ->field($model, 'username')
                ->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
              <?= $form
                ->field($model, 'email')
                ->textInput(['maxlength' => true]) ?>
            </div>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <?= $form
                ->field($model, 'new_password')
                ->passwordInput([
                  'maxlength' => true,
                  'placeholder' => 'Leave blank to keep current password',
                ])
                ->label('New Password (optional)') ?>
            </div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <div class="form-control-plaintext fw-semibold">
                <?= $model->getStatusBadge() ?>
              </div>
            </div>
          </div>
          <div class="text-end mt-4">
            <?= Html::submitButton('Save changes', [
              'class' => 'btn btn-primary px-4',
            ]) ?>
          </div>
        <?php ActiveForm::end(); ?>
      </div>
    </div>
  </div>
</div>