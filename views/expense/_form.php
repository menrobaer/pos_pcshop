<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Expense $model */
/** @var yii\widgets\ActiveForm $form */

$isDuplicate = $isDuplicate ?? false;
$items = $items ?? $model->items;
?>

<div class="expense-form">

    <?php
    $formOptions = ['id' => 'expense-form'];
    if ($isDuplicate) {
      $formOptions['action'] = ['expense/create'];
    }
    $form = ActiveForm::begin($formOptions);
    ?>
<div class="card">
  <div class="card-body">
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'code')->textInput(['readonly' => true]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'supplier_id')->dropDownList($suppliers, [
              'prompt' => 'Select Supplier',
              'class' => 'form-control has-select2',
            ]) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'date')->textInput([
              'data-provider' => 'flatpickr',
              'data-date-format' => 'Y-m-d',
              'data-altFormat' => 'd M, Y',
            ]) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'due_date')->textInput([
              'data-provider' => 'flatpickr',
              'data-date-format' => 'Y-m-d',
              'data-altFormat' => 'd M, Y',
            ]) ?>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <h5>Items</h5>
            <div class="table-responsive">
                <table class="table table-bordered" id="items-table">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 35%">Title</th>
                             <th style="width: 25%">Description</th>
                            <th style="width: 10%">Qty</th>
                            <th style="width: 15%">Price</th>
                            <th style="width: 10%">Total</th>
                            <th style="width: 5%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($items)): ?>
                          <?php foreach ($items as $index => $item): ?>
                                <tr data-index="<?= $index ?>">
                                    <td><?= Html::textInput(
                                      "ExpenseItem[$index][title]",
                                      $item->title,
                                      [
                                        'class' => 'form-control title',
                                      ],
                                    ) ?></td>
                                    <td><?= Html::textarea(
                                      "ExpenseItem[$index][description]",
                                      $item->description,
                                      [
                                        'class' =>
                                          'form-control form-control-sm description auto-height',
                                        'rows' => 2,
                                        'placeholder' => 'Description',
                                      ],
                                    ) ?></td>
                                    <td><?= Html::textInput(
                                      "ExpenseItem[$index][quantity]",
                                      $item->quantity,
                                      [
                                        'class' => 'form-control qty',
                                        'type' => 'number',
                                      ],
                                    ) ?></td>
                                    <td><?= Html::textInput(
                                      "ExpenseItem[$index][price]",
                                      $item->price,
                                      [
                                        'class' => 'form-control price',
                                        'type' => 'number',
                                        'step' => '0.01',
                                      ],
                                    ) ?></td>
                                    <td class="item-total fw-bold fs-6"><?= number_format(
                                      $item->quantity * $item->price,
                                      2,
                                    ) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm remove-item"><i class="ri-delete-bin-line"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6">
                                <button type="button" class="btn btn-info btn-sm" id="add-item"><i class="ri-add-line"></i> Add Item</button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <?= $form->field($model, 'remark')->textarea(['rows' => 4]) ?>
        </div>
        <div class="col-md-6">
            <div class="card p-3 bg-light">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold fs-5 text-primary">Sub Total:</span>
                    <span id="sub-total-display" class="fw-bold fs-5 text-primary">0.00</span>
                </div>
                <?= $form
                  ->field($model, 'sub_total')
                  ->hiddenInput(['id' => 'sub-total-input'])
                  ->label(false) ?>

                <div class="row mb-2">
                    <div class="col-6">Delivery Fee:</div>
                    <div class="col-6">
                        <?= Html::activeTextInput($model, 'delivery_fee', [
                          'class' =>
                            'form-control form-control-sm text-end cost-calc',
                          'type' => 'number',
                          'step' => '0.01',
                        ]) ?>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-6">Extra Charge:</div>
                    <div class="col-6">
                        <?= Html::activeTextInput($model, 'extra_charge', [
                          'class' =>
                            'form-control form-control-sm text-end cost-calc',
                          'type' => 'number',
                          'step' => '0.01',
                        ]) ?>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-2 pt-2 border-top text-success">
                    <span class="h4 fw-bold">Grand Total:</span>
                    <span class="h4 fw-bold" id="grand-total-display">0.00</span>
                </div>
                <?= $form
                  ->field($model, 'grand_total')
                  ->hiddenInput(['id' => 'grand-total-input'])
                  ->label(false) ?>
            </div>
        </div>
    </div>

    <div class="d-flex mt-4 gap-3">
        <?= Html::a(
          'Cancel',
          ['index'],
          ['class' => 'btn btn-light px-5 rounded-pill'],
        ) ?>
        <?php
        $isDuplicate = $isDuplicate ?? false;
        $submitLabel = $model->isNewRecord
          ? ($isDuplicate
            ? 'Duplicate Expense'
            : 'Create Expense')
          : 'Update Expense';
        ?>
        <?= Html::submitButton($submitLabel, [
          'class' => 'btn btn-success text-uppercase rounded-pill px-5',
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
</div>
</div>

<?php
$js = <<<JS
var itemIndex = $('#items-table tbody tr').length;

function initAutoHeight() {
    $('.auto-height').on('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.max(this.scrollHeight, 50) + 'px';
    }).trigger('input');
}

initAutoHeight();

$('#add-item').on('click', function() {
    var row = `
        <tr data-index="\${itemIndex}">
            <td><input type="text" name="ExpenseItem[\${itemIndex}][title]" class="form-control title"></td>
        <td><textarea name="ExpenseItem[\${itemIndex}][description]" class="form-control form-control-sm description auto-height" rows="2" placeholder="Description"></textarea></td>
            <td><input type="number" name="ExpenseItem[\${itemIndex}][quantity]" class="form-control qty" value="1"></td>
            <td><input type="number" name="ExpenseItem[\${itemIndex}][price]" class="form-control price" step="0.01" value="0"></td>
            <td class="item-total fw-bold fs-6">0.00</td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-item"><i class="ri-delete-bin-line"></i></button>
            </td>
        </tr>
    `;
    var \$row = $(row);
    $('#items-table tbody').append(\$row);
    initAutoHeight();
    itemIndex++;
});

$(document).on('click', '.remove-item', function() {
    $(this).closest('tr').remove();
    calculateTotals();
});

$(document).on('input change', '.qty, .price, .cost-calc', function() {
    calculateTotals();
});

function calculateTotals() {
    var subTotal = 0;

    $('#items-table tbody tr').each(function() {
        var qty = parseFloat($(this).find('.qty').val()) || 0;
        var price = parseFloat($(this).find('.price').val()) || 0;

        var lineTotal = qty * price;
        $(this).find('.item-total').text(lineTotal.toFixed(2));
        subTotal += lineTotal;
    });

    $('#sub-total-display').text(subTotal.toFixed(2));
    $('#sub-total-input').val(subTotal);

    var deliveryFee = parseFloat($('[name="Expense[delivery_fee]"]').val()) || 0;
    var extraCharge = parseFloat($('[name="Expense[extra_charge]"]').val()) || 0;

    var grandTotal = subTotal + deliveryFee + extraCharge;
    $('#grand-total-display').text(grandTotal.toFixed(2));
    $('#grand-total-input').val(grandTotal);
}

calculateTotals();
JS;
$this->registerJs($js);


?>
