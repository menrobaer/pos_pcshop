<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Sign In';
$this->context->layout = 'auth';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6 col-xl-5">
        <div class="card mt-4">

            <div class="card-body p-4">
                <div class="text-center mt-2">
                    <h5 class="text-primary">Welcome Back !</h5>
                    <p class="text-muted">Sign in to continue to VLC - POS</p>
                </div>
                <div class="p-2 mt-4">
                    <?php $form = ActiveForm::begin([
                      'id' => 'login-form',
                      'options' => [
                        'class' => 'needs-validation',
                        'autocomplete' => 'off',
                      ],
                      'fieldConfig' => [
                        'template' => "{label}\n{input}\n{error}",
                        'labelOptions' => ['class' => 'form-label'],
                        'inputOptions' => ['class' => 'form-control'],
                        'errorOptions' => ['class' => 'invalid-feedback'],
                      ],
                    ]); ?>

                    <?= $form->field($model, 'username')->textInput([
                      'placeholder' => 'Enter username',
                      'autofocus' => true,
                      'autocomplete' => 'off',
                    ]) ?>

                    <div class="mb-3">
                        <?= $form
                          ->field($model, 'password', [
                            'template' =>
                              "{label}\n<div class=\"position-relative auth-pass-inputgroup mb-3\">\n{input}\n<button class=\"btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon\" type=\"button\" id=\"password-addon\"><i class=\"ri-eye-fill align-middle\"></i></button>\n{error}\n</div>",
                          ])
                          ->passwordInput([
                            'placeholder' => 'Enter password',
                            'class' => 'form-control pe-5 password-input',
                            'autocomplete' => 'off',
                          ]) ?>
                    </div>

                    <div class="form-check">
                        <?= $form->field($model, 'rememberMe')->checkbox([
                          'template' =>
                            "<div class=\"form-check\">\n{input}\n{label}\n{error}\n</div>",
                          'labelOptions' => ['class' => 'form-check-label'],
                          'inputOptions' => ['class' => 'form-check-input'],
                        ]) ?>
                    </div>

                    <div class="mt-4">
                        <?= Html::submitButton('Sign In', [
                          'class' => 'btn btn-success w-100',
                          'name' => 'login-button',
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
            <!-- end card body -->
        </div>
        <!-- end card -->
    </div>
</div>

<?php
$script = <<<JS
document.getElementById("password-addon").addEventListener("click", function () {
    var passwordInput = document.querySelector(".password-input");
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
    } else {
        passwordInput.type = "password";
    }
});
JS;
$this->registerJs($script);


?>
