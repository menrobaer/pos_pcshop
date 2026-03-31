<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Service $model */
/** @var yii\widgets\ActiveForm $form */

$items = $items ?? $model->items;
?>

<div class="service-form">

  <?php
  $form = ActiveForm::begin(['id' => 'service-form']);
  ?>

  <div class="row">
    <div class="col-md-2">
      <?= $form->field($model, 'code')->textInput(['readonly' => true]) ?>
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
    <div class="col-md-2">
      <?= $form->field($model, 'customer_id')->dropDownList($customers, [
        'prompt' => 'Select Customer',
        'class' => 'form-control has-select2',
        'value' => $model->isNewRecord ? 1 : $model->customer_id,
      ]) ?>
    </div>
    <div class="col-md-2">
      <?= $form->field($model, 'phone')->textInput() ?>
    </div>
    <div class="col-md-2">
      <?= $form->field($model, 'address')->textInput() ?>
    </div>
  </div>

  <div class="row mt-4">
    <div class="col-md-12">
      <h5>Service Items</h5>
      <div class="table-responsive">
        <table class="table table-bordered" id="items-table">
          <thead class="bg-light">
            <tr>
              <th style="width: 25%">Service Name</th>
              <th style="width: 15%">Serial</th>
              <th style="width: 8%">Cost</th>
              <th style="width: 7%">Qty</th>
              <th style="width: 8%">Price</th>
              <th style="width: 10%">Discount</th>
              <th style="width: 12%">Total</th>
              <th style="width: 5%"></th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($items)): ?>
              <?php foreach ($items as $index => $item): ?>
                <tr data-index="<?= $index ?>">
                  <td>
                    <?= Html::textInput(
                      "ServiceItem[$index][name]",
                      $item->name,
                      ['class' => 'form-control service-name'],
                    ) ?>
                    <div class="mt-1">
                      <?= Html::textarea(
                        "ServiceItem[$index][description]",
                        $item->description ?? '',
                        [
                          'class' => 'form-control form-control-sm description auto-height',
                          'rows' => 4,
                          'placeholder' => 'Description',
                        ],
                      ) ?>
                    </div>
                    <?= Html::hiddenInput(
                      "ServiceItem[$index][id]",
                      $item->id,
                    ) ?>
                  </td>
                  <td>
                    <?= Html::textInput(
                      "ServiceItem[$index][unit]",
                      $item->unit,
                      ['class' => 'form-control'],
                    ) ?>
                  </td>
                  <td>
                    <?= Html::textInput(
                      "ServiceItem[$index][cost]",
                      $item->cost,
                      [
                        'class' => 'form-control item-cost',
                        'type' => 'number',
                      ],
                    ) ?>
                  </td>
                  <td>
                    <?= Html::textInput(
                      "ServiceItem[$index][quantity]",
                      $item->quantity,
                      [
                        'class' => 'form-control item-quantity',
                        'type' => 'number',
                      ],
                    ) ?>
                  </td>
                  <td>
                    <?= Html::textInput(
                      "ServiceItem[$index][price]",
                      $item->price,
                      [
                        'class' => 'form-control item-price',
                        'type' => 'number',
                        'step' => '0.01',
                      ],
                    ) ?>
                  </td>
                  <td>
                    <div class="input-group">
                      <?= Html::textInput(
                        "ServiceItem[$index][discount]",
                        $item->discount,
                        [
                          'class' => 'form-control item-discount',
                          'type' => 'number',
                          'step' => '0.01',
                        ],
                      ) ?>
                      <button type="button" class="btn btn-outline-secondary toggle-discount-type">
                        <?= $item->discount_type === 'percent'
                          ? '%'
                          : '$' ?>
                      </button>
                      <?= Html::hiddenInput(
                        "ServiceItem[$index][discount_type]",
                        $item->discount_type ?: 'fixed',
                        ['class' => 'discount-type'],
                      ) ?>
                    </div>
                  </td>
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
              <td colspan="8">
                <button type="button" class="btn btn-info btn-sm" id="add-item"><i class="ri-add-line"></i> Add Item</button>
              </td>
            </tr>
          </tfoot>
        </table>
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

          <div class="d-flex justify-content-between mb-2">
            <span class="fw-bold fs-6">Total Discount:</span>
            <span id="total-discount-display" class="fw-bold fs-6">0.00</span>
          </div>
          <?= $form
            ->field($model, 'discount_amount')
            ->hiddenInput(['id' => 'discount-amount-input'])
            ->label(false) ?>

          <div class="row mb-2">
            <div class="col-6">Delivery Fee:</div>
            <div class="col-6">
              <?= Html::activeTextInput($model, 'delivery_fee', [
                'class' => 'form-control form-control-sm text-end cost-calc',
                'type' => 'number',
                'step' => '0.01',
              ]) ?>
            </div>
          </div>

          <div class="row mb-2">
            <div class="col-6">Extra Charge:</div>
            <div class="col-6">
              <?= Html::activeTextInput($model, 'extra_charge', [
                'class' => 'form-control form-control-sm text-end cost-calc',
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
      $submitLabel = $model->isNewRecord ? 'Create Service' : 'Update Service';
      ?>
      <?= Html::submitButton($submitLabel, [
        'class' => 'btn btn-success text-uppercase rounded-pill px-5',
      ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

  </div>
</div>
<?php
$this->registerJsVar('isNewRecord', $model->isNewRecord);
$this->registerJs(
  <<<JS
$(document).ready(function() {
    let itemIndex = $('#items-table tbody tr').length;

    function calculateItemTotal(row) {
        const price = parseFloat($(row).find('.item-price').val()) || 0;
        const qty = parseInt($(row).find('.item-quantity').val()) || 1;
        const discount = parseFloat($(row).find('.item-discount').val()) || 0;
        const discountType = $(row).find('.discount-type').val();

        let itemTotal = price * qty;
        if (discountType === 'percent') {
            itemTotal -= (itemTotal * discount) / 100;
        } else {
            itemTotal -= discount * qty;
        }

        $(row).find('.item-total').text(itemTotal.toFixed(2));
        calculateTotals();
    }

    function calculateTotals() {
        let subTotal = 0;
        let costTotal = 0;

        $('#items-table tbody tr').each(function() {
            const total = parseFloat($(this).find('.item-total').text()) || 0;
            const cost = parseFloat($(this).find('.item-cost').val()) || 0;
            const qty = parseInt($(this).find('.item-quantity').val()) || 0;

            subTotal += total;
            costTotal += cost * qty;
        });

        const deliveryFee = parseFloat($('[name="Service[delivery_fee]"]').val()) || 0;
        const extraCharge = parseFloat($('[name="Service[extra_charge]"]').val()) || 0;
        const discountAmount = parseFloat($('[name="Service[discount_amount]"]').val()) || 0;

        const grandTotal = subTotal + deliveryFee + extraCharge - discountAmount;

        $('#sub-total-display').text(subTotal.toFixed(2));
        $('#sub-total-input').val(subTotal.toFixed(2));
        $('#total-discount-display').text(discountAmount.toFixed(2));
        $('#discount-amount-input').val(discountAmount.toFixed(2));
        $('#grand-total-display').text(grandTotal.toFixed(2));
        $('#grand-total-input').val(grandTotal.toFixed(2));
    }

    // Event handlers
    $(document).on('change', '.item-cost, .item-quantity, .item-price, .item-discount, .discount-type', function() {
        calculateItemTotal($(this).closest('tr'));
    });

    $(document).on('change', '[name="Service[delivery_fee]"], [name="Service[extra_charge]"], [name="Service[discount_amount]"]', function() {
        calculateTotals();
    });

    // Toggle discount type button
    $(document).on('click', '.toggle-discount-type', function(e) {
        e.preventDefault();
        const row = $(this).closest('tr');
        const discountTypeInput = row.find('.discount-type');
        const currentType = discountTypeInput.val();
        const newType = currentType === 'percent' ? 'fixed' : 'percent';
        
        discountTypeInput.val(newType);
        $(this).text(newType === 'percent' ? '%' : '\$');
        calculateItemTotal(row);
    });

    // Add item button using delegated binding
    $(document).on('click', '#add-item', function(e) {
        e.preventDefault();
        const newRow = `
            <tr data-index="\${itemIndex}">
                <td>
                    <input type="text" name="ServiceItem[\${itemIndex}][name]" class="form-control service-name" placeholder="Service name">
                    <div class="mt-1">
                        <textarea name="ServiceItem[\${itemIndex}][description]" class="form-control form-control-sm description auto-height" rows="4" placeholder="Description"></textarea>
                    </div>
                </td>
                <td>
                    <input type="text" name="ServiceItem[\${itemIndex}][serial]" class="form-control" placeholder="Serial">
                </td>
                <td>
                    <input type="number" name="ServiceItem[\${itemIndex}][cost]" class="form-control text-end item-cost" step="0.01" value="0.00">
                </td>
                <td>
                    <input type="number" name="ServiceItem[\${itemIndex}][quantity]" class="form-control text-center item-quantity" min="1" value="1">
                </td>
                <td>
                    <input type="number" name="ServiceItem[\${itemIndex}][price]" class="form-control text-end item-price" step="0.01" value="0.00">
                </td>
                <td>
                    <div class="input-group">
                        <input type="number" name="ServiceItem[\${itemIndex}][discount]" class="form-control text-end item-discount" step="0.01" value="0.00">
                        <button type="button" class="btn btn-outline-secondary toggle-discount-type">\$</button>
                        <input type="hidden" name="ServiceItem[\${itemIndex}][discount_type]" class="discount-type" value="fixed">
                    </div>
                </td>
                <td class="text-end item-total fw-bold">0.00</td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-item" title="Remove">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#items-table tbody').append(newRow);
        itemIndex++;
        calculateTotals();
        return false;
    });

    $(document).on('click', '.remove-item', function() {
        $(this).closest('tr').remove();
        calculateTotals();
    });

    // Initialize totals on page load
    calculateTotals();

    // Auto-add first row for new records
    if (isNewRecord && $('#items-table tbody tr').length === 0) {
        $('#add-item').click();
    }
});
JS
);
?>