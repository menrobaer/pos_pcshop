<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\User $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin([
      'id' => 'user-form',
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
    <div class="row">
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

    <div class="row">
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

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'role_id')->dropDownList(
              [
                1 => 'Admin',
                2 => 'Manager',
                3 => 'Staff',
              ],
              ['prompt' => 'Select Role'],
            ) ?>
        </div>
        <div class="col-md-6">
            <?= $form
              ->field($model, 'new_password')
              ->passwordInput([
                'maxlength' => true,
                'placeholder' => $model->isNewRecord
                  ? 'Enter password'
                  : 'Leave empty to keep current password',
              ])
              ->label($model->isNewRecord ? 'Password' : 'New Password') ?>
        </div>
    </div>

    <?= $form
      ->field($model, 'status', [
        'template' => '<label class="form-check-label" for="checkbox-user-status">Status</label>
        <div class="form-check form-switch">{input}<label class="form-check-label" for="checkbox-user-status">Active</label></div>{error}{hint}',
      ])
      ->checkbox(
        [
          'class' => 'form-check-input',
          'role' => 'switch',
          'id' => 'checkbox-user-status',
          'label' => false,
        ],
        false,
      ) ?>

    <div class="d-flex mt-4 gap-3">
        <?= Html::button('Cancel', [
          'class' => 'btn btn-light px-5 rounded-pill',
          'id' => 'btn-dismiss-modal',
        ]) ?>
        <?= Html::submitButton(
          $model->isNewRecord ? 'Create User' : 'Update User',
          ['class' => 'btn btn-dark text-uppercase rounded-pill px-5'],
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
