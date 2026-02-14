<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Invoice $model */
/** @var yii\widgets\ActiveForm $form */

$isDuplicate = $isDuplicate ?? false;
$items = $items ?? $model->items;
?>

<div class="invoice-form">

    <?php
    $formOptions = ['id' => 'invoice-form'];
    if ($isDuplicate) {
        $formOptions['action'] = ['invoice/create'];
    }
    $form = ActiveForm::begin($formOptions);
    ?>

    <?= $form->field($model, 'quotation_id')->hiddenInput()->label(false) ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'code')->textInput(['readonly' => true]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'customer_id')->dropDownList($customers, [
                'prompt' => 'Select Customer',
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
                            <th style="width: 35%">Product</th>
                            <th style="width: 10%">Serial</th>
                            <th style="width: 8%">Cost</th>
                            <th style="width: 7%">Qty</th>
                            <th style="width: 10%">Price</th>
                            <th style="width: 13%">Discount</th>
                            <th style="width: 12%">Total</th>
                            <th style="width: 5%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($items)): ?>
                            <?php foreach ($items as $index => $item): ?>
                                <tr data-index="<?= $index ?>">
                                    <td>
                                        <?= Html::dropDownList(
                                            "InvoiceItem[$index][product_id]",
                                            $item->product_id,
                                            [
                                                $item->product_id =>
                                                $item->product_name .
                                                    ($item->sku
                                                        ? ' (' . $item->sku . ')'
                                                        : ''),
                                            ],
                                            [
                                                'class' =>
                                                'form-control product-select select2-ajax',
                                                'prompt' => 'Select Product',
                                            ],
                                        ) ?>
                                        <?= Html::hiddenInput(
                                            "InvoiceItem[$index][product_name]",
                                            $item->product_name,
                                            ['class' => 'product-name'],
                                        ) ?>
                                        <div class="mt-1">
                                            <?= Html::textarea(
                                                "InvoiceItem[$index][description]",
                                                $item->description,
                                                [
                                                    'class' =>
                                                    'form-control form-control-sm description auto-height',
                                                    'rows' => 4,
                                                    'placeholder' => 'Description',
                                                ],
                                            ) ?>
                                        </div>
                                    </td>
                                    <td><?= Html::textInput(
                                            "InvoiceItem[$index][serial]",
                                            $item->serial,
                                            [
                                                'class' => 'form-control serial',
                                                'readonly' => true,
                                            ],
                                        ) ?></td>
                                    <td><?= Html::textInput(
                                            "InvoiceItem[$index][cost]",
                                            $item->cost,
                                            [
                                                'class' => 'form-control cost',
                                                'type' => 'number',
                                            ],
                                        ) ?></td>
                                    <td><?= Html::textInput(
                                            "InvoiceItem[$index][quantity]",
                                            $item->quantity,
                                            [
                                                'class' => 'form-control qty',
                                                'type' => 'number',
                                            ],
                                        ) ?></td>
                                    <td><?= Html::textInput(
                                            "InvoiceItem[$index][full_price]",
                                            $item->full_price,
                                            [
                                                'class' => 'form-control full-price',
                                                'type' => 'number',
                                                'step' => '0.01',
                                            ],
                                        ) ?>
                                        <?= Html::hiddenInput(
                                            "InvoiceItem[$index][price]",
                                            $item->price,
                                            ['class' => 'price'],
                                        ) ?></td>
                                    <td>
                                        <div class="input-group">
                                            <?= Html::textInput(
                                                "InvoiceItem[$index][discount]",
                                                $item->discount,
                                                [
                                                    'class' =>
                                                    'form-control discount',
                                                    'type' => 'number',
                                                    'step' => '0.01',
                                                ],
                                            ) ?>
                                            <button type="button" class="btn btn-outline-secondary toggle-discount-type">
                                                <?= $item->discount_type ===
                                                    'percentage'
                                                    ? '%'
                                                    : '$' ?>
                                            </button>
                                            <?= Html::hiddenInput(
                                                "InvoiceItem[$index][discount_type]",
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
                                        <?= Html::hiddenInput(
                                            "InvoiceItem[$index][unit]",
                                            $item->unit,
                                            ['class' => 'unit'],
                                        ) ?>
                                        <?= Html::hiddenInput(
                                            "InvoiceItem[$index][sku]",
                                            $item->sku,
                                            ['class' => 'sku'],
                                        ) ?>
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
                ? 'Duplicate Invoice'
                : 'Create Invoice')
            : 'Update Invoice';
        ?>
        <?= Html::submitButton($submitLabel, [
            'class' => 'btn btn-success text-uppercase rounded-pill px-5',
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$productSearchUrl = Url::to(['invoice/product-search']);
$js = <<<JS
var itemIndex = $('#items-table tbody tr').length;

function initProductSelect2(element) {
    element.select2({
        ajax: {
            url: '{$productSearchUrl}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return { results: data.results };
            },
            cache: true
        },
        minimumInputLength: 3,
        placeholder: 'Search Product...',
        allowClear: false,
        templateSelection: function (data) {
            return data.text || data.element.text;
        }
    }).on('select2:select', function (e) {
        var data = e.params.data.data;
        var row = $(this).closest('tr');
        row.find('.product-name').val(data.name);
        row.find('.sku').val(data.sku);
        row.find('.full-price').val(data.price);
        row.find('.price').val(data.price);
        row.find('.cost').val(data.cost);
        row.find('.serial').val(data.serial || '-');
        row.find('.description').val(data.description || '').trigger('input');
        calculateTotals();
    });
}

function initAutoHeight() {
    $('.auto-height').on('input', function() {
        this.style.height = 'auto';
        var minHeight = 4 * 1.5 * 14; // Approximate 4 rows (1.5 line-height * 14px font)
        this.style.height = Math.max(this.scrollHeight, 80) + 'px'; // 80px is approx 4 rows
    }).trigger('input');
}

initAutoHeight();

$('.product-select.select2-ajax').each(function() {
    initProductSelect2($(this));
});

$('#add-item').on('click', function() {
    var row = `
        <tr data-index="\${itemIndex}">
            <td>
                <select name="InvoiceItem[\${itemIndex}][product_id]" class="form-control product-select select2-ajax">
                    <option value="">Select Product</option>
                </select>
                <input type="hidden" name="InvoiceItem[\${itemIndex}][product_name]" class="product-name">
                <div class="mt-1">
                    <textarea name="InvoiceItem[\${itemIndex}][description]" class="form-control form-control-sm description auto-height" rows="4" placeholder="Description"></textarea>
                </div>
            </td>
            <td><input type="text" name="InvoiceItem[\${itemIndex}][serial]" class="form-control serial" readonly></td>
            <td><input type="number" name="InvoiceItem[\${itemIndex}][cost]" class="form-control cost"></td>
            <td><input type="number" name="InvoiceItem[\${itemIndex}][quantity]" class="form-control qty" value="1"></td>
            <td><input type="number" name="InvoiceItem[\${itemIndex}][full_price]" class="form-control full-price" step="0.01">
                <input type="hidden" name="InvoiceItem[\${itemIndex}][price]" class="price">
            </td>
            <td>
                <div class="input-group">
                    <input type="number" name="InvoiceItem[\${itemIndex}][discount]" class="form-control discount" step="0.01" value="0">
                    <button type="button" class="btn btn-outline-secondary toggle-discount-type">$</button>
                    <input type="hidden" name="InvoiceItem[\${itemIndex}][discount_type]" class="discount-type" value="fixed">
                </div>
            </td>
            <td class="item-total fw-bold fs-6">0.00</td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-item"><i class="ri-delete-bin-line"></i></button>
                <input type="hidden" name="InvoiceItem[\${itemIndex}][unit]" class="unit" value="unit">
                <input type="hidden" name="InvoiceItem[\${itemIndex}][sku]" class="sku">
            </td>
        </tr>
    `;
    var \$row = $(row);
    $('#items-table tbody').append(\$row);
    initProductSelect2(\$row.find('.product-select'));
    initAutoHeight();
    itemIndex++;
});

$(document).on('click', '.remove-item', function() {
    $(this).closest('tr').remove();
    calculateTotals();
});

$(document).on('click', '.toggle-discount-type', function() {
    var btn = $(this);
    var input = btn.siblings('.discount-type');
    if (input.val() === 'fixed') {
        input.val('percentage');
        btn.text('%');
    } else {
        input.val('fixed');
        btn.text('$');
    }
    calculateTotals();
});

$(document).on('input change', '.qty, .full-price, .discount, .discount-type, .cost-calc', function() {
    calculateTotals();
});

function calculateTotals() {
    var subTotal = 0;
    var totalDiscount = 0;

    $('#items-table tbody tr').each(function() {
        var qty = parseFloat($(this).find('.qty').val()) || 0;
        var fullPrice = parseFloat($(this).find('.full-price').val()) || 0;
        var discountValue = parseFloat($(this).find('.discount').val()) || 0;
        var discountType = $(this).find('.discount-type').val();

        var lineGross = qty * fullPrice;
        var lineDiscount = 0;

        if (discountType === 'fixed') {
          lineDiscount = discountValue * qty;
        } else if (discountType === 'percentage') {
            lineDiscount = (lineGross * discountValue / 100);
        }

        var lineNet = lineGross - lineDiscount;
        var netUnitPrice = qty > 0 ? (lineNet / qty) : fullPrice;

        $(this).find('.price').val(netUnitPrice);
        $(this).find('.item-total').text(lineNet.toFixed(2));
        subTotal += lineGross;
        totalDiscount += lineDiscount;
    });

    $('#sub-total-display').text(subTotal.toFixed(2));
    $('#sub-total-input').val(subTotal);

    $('#total-discount-display').text(totalDiscount.toFixed(2));
    $('#discount-amount-input').val(totalDiscount);

    var deliveryFee = parseFloat($('[name="Invoice[delivery_fee]"]').val()) || 0;
    var extraCharge = parseFloat($('[name="Invoice[extra_charge]"]').val()) || 0;

    var grandTotal = subTotal - totalDiscount + deliveryFee + extraCharge;
    $('#grand-total-display').text(grandTotal.toFixed(2));
    $('#grand-total-input').val(grandTotal);
}

calculateTotals();

$('#invoice-date').on('change', function() {
    var date = $(this).val();
    if (date) {
        var dueDateElem = document.querySelector('#invoice-due_date');
        if (dueDateElem && dueDateElem._flatpickr) {
            dueDateElem._flatpickr.set('minDate', date);
        }
    }
});
JS;
$this->registerJs($js);

if ($model->isNewRecord) {
    $this->registerJs("$('#add-item').trigger('click');");
}
?>