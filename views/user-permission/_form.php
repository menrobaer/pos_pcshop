<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\UserRole $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="user-role-form">

  <?php $form = ActiveForm::begin(); ?>

  <?= $form
    ->field($model, 'name')
    ->textInput(['maxlength' => true, 'placeholder' => 'Enter role name']) ?>

  <div class="card-title"><?= Yii::t(
                            'app',
                            'Choose what this role can access',
                          ) ?></div>

  <table class="table table-condensed">
    <thead class="thead-light">
      <tr>
        <th>Feature</th>
        <th>Capabilities</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($userRoleActionByGroup)) {
        foreach ($userRoleActionByGroup as $key => $value) { ?>
          <tr>
            <td><?= $key ?></td>
            <td>
              <?php foreach ($value as $k => $v) {
                $unique_key = 'chkboxAction_' . $key . $k;
                $checked = $v['checked'] == 1 ? 'checked' : '';
                $currentVal = '';
                if ($v['checked'] == 1) {
                  $currentVal = $v['id'];
                }
                echo "<div class='form-check form-switch mb-1'>
                              <input {$checked} type='checkbox' class='form-check-input chkboxAction' data-val='{$v['id']}' id='{$unique_key}' role='switch'>
                              <input type='hidden' data-id='{$unique_key}' name='chkboxAction[]' value='{$currentVal}' />
                              <label class='form-check-label ms-1' for='{$unique_key}'>{$v['name']}</label>
                            </div>";
              } ?>
            </td>
          </tr>
      <?php }
      } ?>
    </tbody>
  </table>

  <div class="d-flex mt-4 gap-3">
    <?= Html::button('Cancel', [
      'class' => 'btn btn-light px-5 rounded-pill',
      'id' => 'btn-dismiss-modal',
    ]) ?>
    <?= Html::submitButton(
      $model->isNewRecord ? 'Create Role' : 'Update Role',
      ['class' => 'btn btn-dark text-uppercase rounded-pill px-5', 'id' => 'submit-btn'],
    ) ?>
  </div>

  <?php ActiveForm::end(); ?>
</div>

<?php
$js = <<<JS
$(document).on('change', '.chkboxAction', function() {
    var isChecked = $(this).is(':checked');
    var val = $(this).data('val');
    var id = $(this).attr('id');
    var hiddenInput = $('input[data-id="' + id + '"]');

    if (isChecked) {
        hiddenInput.val(val);
    } else {
        hiddenInput.val('');
    }
});

// Validate at least one permission is selected before submission
$('form').on('submit', function(e) {
    var checkedCount = $('input[name="chkboxAction[]"]:hidden').filter(function() {
        return $(this).val() !== '';
    }).length;
    
    if (checkedCount === 0) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'No Permissions Selected',
            text: 'Please select at least one permission for this role.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545'
        });
        return false;
    }
});
JS;
$this->registerJs($js);


?>