<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Quotation $model */
/** @var yii\widgets\ActiveForm $form */

$isDuplicate = $isDuplicate ?? false;
$items = $items ?? $model->items;

$minItemRows = 1;
if (count($items) < $minItemRows) {
    for ($i = count($items); $i < $minItemRows; $i++) {
        $emptyItem = new \app\models\QuotationItem();
        $emptyItem->quantity = 1;
        $emptyItem->discount_type = 'fixed';
        $emptyItem->discount = 0;
        $items[] = $emptyItem;
    }
}
?>

<div class="quotation-form quotation-sheet-shell">
    <div class="quotation-sheet quotation-sheet-page">

    <?php
    $formOptions = ['id' => 'quotation-form'];
    if ($isDuplicate) {
        $formOptions['action'] = ['quotation/create'];
    }
    $form = ActiveForm::begin($formOptions);

    $submitLabel = $model->isNewRecord
        ? ($isDuplicate
            ? 'Duplicate Quotation'
            : 'Create Quotation')
        : 'Update Quotation';
    ?>

    <div class="card quotation-sheet-card" id="quotation-form-sheet">
        <div class="card-header border-bottom-dashed p-3">
            <div class="d-flex justify-content-end align-items-center gap-2 form-action-strip">
                <?= Html::a(
                    '<i class="ri-arrow-left-line align-bottom me-1"></i> Back',
                    ['index'],
                    ['class' => 'btn btn-light btn-sm'],
                ) ?>
                <button type="button" class="btn btn-soft-info btn-sm" id="add-item-top"><i class="ri-add-line align-bottom me-1"></i> Add Item</button>
                <?= Html::submitButton('<i class="ri-save-line align-bottom me-1"></i> ' . $submitLabel, [
                    'class' => 'btn btn-primary btn-sm',
                ]) ?>
            </div>
        </div>

        <div class="card-body p-4 border-top border-top-dashed">
            <div class="row g-3 align-items-start quotation-headband">
                <div class="col-md-4">
                    <p class="fw-bold mb-2">Quote To</p>
                    <div class="quotation-meta-grid">
                        <?= $form->field($model, 'customer_id')->dropDownList($customers, [
                            'prompt' => 'Select Customer',
                            'class' => 'form-control has-select2',
                        ]) ?>
                        <?= $form->field($model, 'phone')->textInput() ?>
                        <?= $form->field($model, 'address')->textInput() ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center quotation-form-title-wrap">
                        <h3 class="fw-bold mb-0 quotation-title">សម្រង់តម្លៃ<br>Quotation</h3>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <div id="barcode-container" class="mb-3">
                        <div><svg id="quotation-barcode"></svg></div>
                        <div id="quotation-code"><?= Html::encode($model->code ?: 'DRAFT') ?></div>
                    </div>
                    <div class="quotation-meta-grid quotation-meta-grid-right">
                        <?= $form->field($model, 'code')->textInput([
                            'readonly' => true,
                            'id' => 'quotation-code-input',
                        ]) ?>
                        <?= $form->field($model, 'date')->textInput([
                            'data-provider' => 'flatpickr',
                            'data-date-format' => 'Y-m-d',
                            'data-altFormat' => 'd M, Y',
                        ]) ?>
                        <?= $form->field($model, 'due_date')->textInput([
                            'data-provider' => 'flatpickr',
                            'data-date-format' => 'Y-m-d',
                            'data-altFormat' => 'd M, Y',
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-4 quotation-items-section">
            <div class="table-responsive quotation-sheet-table-wrap">
                <table class="table table-borderless text-center align-top mb-0 quotation-sheet-table" id="items-table">
                    <thead>
                        <tr>
                            <th class="table-active" style="width: 35%">Product</th>
                            <th class="table-active" style="width: 10%">SKU</th>
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
                                <tr data-index="<?= $index ?>" class="quotation-item-row">
                                    <td>
                                        <?= Html::textInput(
                                            "QuotationItem[$index][product_name]",
                                            $item->product_name,
                                            [
                                                'class' => 'form-control',
                                                'placeholder' => 'Enter product name',
                                            ],
                                        ) ?>
                                        <div class="mt-1">
                                            <?= Html::textarea(
                                                "QuotationItem[$index][description]",
                                                $item->description,
                                                [
                                                    'class' =>
                                                    'form-control form-control-sm description auto-height quotation-description',
                                                    'rows' => 8,
                                                    'placeholder' => 'Description',
                                                ],
                                            ) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?= Html::textInput(
                                            "QuotationItem[$index][sku]",
                                            $item->sku,
                                            ['class' => 'form-control sku sku-input'],
                                        ) ?>
                                    </td>
                                    <td><?= Html::textInput(
                                            "QuotationItem[$index][serial]",
                                            $item->serial,
                                            [
                                                'class' => 'form-control serial',
                                            ],
                                        ) ?></td>
                                    <td><?= Html::textInput(
                                            "QuotationItem[$index][cost]",
                                            $item->cost,
                                            [
                                                'class' => 'form-control cost',
                                                'readonly' => true,
                                            ],
                                        ) ?></td>
                                    <td><?= Html::textInput(
                                            "QuotationItem[$index][quantity]",
                                            $item->quantity ?: 1,
                                            [
                                                'class' => 'form-control qty',
                                                'type' => 'number',
                                            ],
                                        ) ?></td>
                                    <td><?= Html::textInput(
                                            "QuotationItem[$index][full_price]",
                                            $item->full_price,
                                            [
                                                'class' => 'form-control full-price',
                                                'type' => 'number',
                                                'step' => '0.01',
                                            ],
                                        ) ?>
                                        <?= Html::hiddenInput(
                                            "QuotationItem[$index][price]",
                                            $item->price,
                                            ['class' => 'price'],
                                        ) ?></td>
                                    <td>
                                        <div class="input-group">
                                            <?= Html::textInput(
                                                "QuotationItem[$index][discount]",
                                                $item->discount ?: 0,
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
                                                "QuotationItem[$index][discount_type]",
                                                $item->discount_type ?: 'fixed',
                                                ['class' => 'discount-type'],
                                            ) ?>
                                        </div>
                                    </td>
                                    <td class="item-total fw-bold fs-6 quotation-item-total"><?= number_format(
                                                                            $item->quantity * $item->price,
                                                                            2,
                                                                        ) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm remove-item"><i class="ri-delete-bin-line"></i></button>
                                        <?= Html::hiddenInput(
                                            "QuotationItem[$index][unit]",
                                            $item->unit,
                                            ['class' => 'unit'],
                                        ) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="9" class="quotation-sheet-table-actions">
                                <button type="button" class="btn btn-info btn-sm" id="add-item"><i class="ri-add-line"></i> Add Item</button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="card-body p-4 quotation-summary-row">
            <div class="border-top border-top-dashed mt-2">
                <table class="table table-borderless table-nowrap align-middle mb-0 ms-auto quotation-summary-table" style="width:250px">
                    <tbody>
                        <tr>
                            <td>Sub Total</td>
                            <td class="text-end" id="sub-total-display">0.00</td>
                        </tr>
                        <tr>
                            <td>Discount</td>
                            <td class="text-end text-danger" id="total-discount-display">0.00</td>
                        </tr>
                        <tr>
                            <td>Delivery Fee</td>
                            <td>
                                <?= Html::activeTextInput($model, 'delivery_fee', [
                                    'class' => 'form-control form-control-sm text-end cost-calc',
                                    'type' => 'number',
                                    'step' => '0.01',
                                ]) ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Extra Charge</td>
                            <td>
                                <?= Html::activeTextInput($model, 'extra_charge', [
                                    'class' => 'form-control form-control-sm text-end cost-calc',
                                    'type' => 'number',
                                    'step' => '0.01',
                                ]) ?>
                            </td>
                        </tr>
                        <tr class="border-top border-top-dashed fs-15 quotation-grand-total-line">
                            <th scope="row">Total Amount</th>
                            <th class="text-end" id="grand-total-display">0.00</th>
                        </tr>
                    </tbody>
                </table>

                <?= $form
                    ->field($model, 'sub_total')
                    ->hiddenInput(['id' => 'sub-total-input'])
                    ->label(false) ?>
                <?= $form
                    ->field($model, 'discount_amount')
                    ->hiddenInput(['id' => 'discount-amount-input'])
                    ->label(false) ?>
                <?= $form
                    ->field($model, 'grand_total')
                    ->hiddenInput(['id' => 'grand-total-input'])
                    ->label(false) ?>
            </div>
        </div>

        <div class="card-body px-4 pb-4 pt-0">
            <div class="mt-4">
                <div class="alert alert-info mb-3">
                    <p class="mb-2"><span class="fw-semibold">NOTES:</span></p>
                    <?= $form->field($model, 'remark')->textarea([
                        'rows' => 4,
                        'class' => 'form-control quotation-remark',
                    ])->label(false) ?>
                </div>
            </div>

            <?php if (!empty($outlet) && !empty($outlet->terms)): ?>
                <div class="mt-4">
                    <h6 class="text-muted text-uppercase fw-semibold mb-2">Terms & Conditions:</h6>
                    <p class="text-muted mb-0"><?= nl2br(Html::encode($outlet->terms)) ?></p>
                </div>
            <?php endif; ?>

            <div class="row font-size-sm invoice-signature quotation-signature mt-4">
                <div class="col-4 offset-1 text-center">Customer / អ្នកទិញ</div>
                <div class="col-4 offset-2 text-center">Sales / អ្នកលក់</div>
            </div>
        </div>

        <div class="card-footer border-top border-top-dashed p-3 d-flex justify-content-end gap-2 quotation-actions">
            <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-light btn-sm']) ?>
            <?= Html::submitButton($submitLabel, [
                'class' => 'btn btn-primary btn-sm',
            ]) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$productSearchUrl = Url::to(['quotation/product-search']);
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
        var minHeight = 8 * 1.5 * 14; // Approximate 8 rows (1.5 line-height * 14px font)
        this.style.height = Math.max(this.scrollHeight, minHeight) + 'px';
    }).trigger('input');
}

initAutoHeight();

$('.product-select.select2-ajax').each(function() {
    initProductSelect2($(this));
});

$('#add-item').on('click', function() {
    var row = `
        <tr data-index="\${itemIndex}" class="quotation-item-row">
            <td>
                <input type="text" name="QuotationItem[\${itemIndex}][product_name]" class="form-control" placeholder="Enter product name">
                <div class="mt-1">
                    <textarea name="QuotationItem[\${itemIndex}][description]" class="form-control form-control-sm description auto-height" rows="8" placeholder="Description"></textarea>
                </div>
            </td>
            <td><input type="text" name="QuotationItem[\${itemIndex}][sku]" class="form-control sku sku-input"></td>
            <td><input type="text" name="QuotationItem[\${itemIndex}][serial]" class="form-control serial"></td>
            <td><input type="text" name="QuotationItem[\${itemIndex}][cost]" class="form-control cost" readonly></td>
            <td><input type="number" name="QuotationItem[\${itemIndex}][quantity]" class="form-control qty" value="1"></td>
            <td><input type="number" name="QuotationItem[\${itemIndex}][full_price]" class="form-control full-price" step="0.01">
                <input type="hidden" name="QuotationItem[\${itemIndex}][price]" class="price">
            </td>
            <td>
                <div class="input-group">
                    <input type="number" name="QuotationItem[\${itemIndex}][discount]" class="form-control discount" step="0.01" value="0">
                    <button type="button" class="btn btn-outline-secondary toggle-discount-type">$</button>
                    <input type="hidden" name="QuotationItem[\${itemIndex}][discount_type]" class="discount-type" value="fixed">
                </div>
            </td>
            <td class="item-total fw-bold fs-6 quotation-item-total">0.00</td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-item"><i class="ri-delete-bin-line"></i></button>
                <input type="hidden" name="QuotationItem[\${itemIndex}][unit]" class="unit" value="unit">
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

    var deliveryFee = parseFloat($('[name="Quotation[delivery_fee]"]').val()) || 0;
    var extraCharge = parseFloat($('[name="Quotation[extra_charge]"]').val()) || 0;

    var grandTotal = subTotal - totalDiscount + deliveryFee + extraCharge;
    $('#grand-total-display').text(grandTotal.toFixed(2));
    $('#grand-total-input').val(grandTotal);
}

calculateTotals();

function ensureMinRows(minRows) {
    while ($('#items-table tbody tr').length < minRows) {
        $('#add-item').trigger('click');
    }
}

function renderQuotationBarcode() {
    if (typeof JsBarcode === 'undefined') {
        return;
    }

    var codeValue = ($('#quotation-code-input').val() || '').trim();
    var displayCode = codeValue || 'DRAFT';
    $('#quotation-code').text(displayCode);

    try {
        JsBarcode('#quotation-barcode', displayCode, {
            format: 'CODE128',
            width: 1,
            height: 20,
            displayValue: false,
            margin: 1
        });
    } catch (e) {
        // silently ignore barcode rendering issues in form mode
    }
}

$('#quotation-date').on('change', function() {
    var date = $(this).val();
    if (date) {
        var dueDateElem = document.querySelector('#quotation-due_date');
        if (dueDateElem && dueDateElem._flatpickr) {
            dueDateElem._flatpickr.set('minDate', date);
        }
    }
});
JS;
$this->registerJsFile('https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJs($js);

$this->registerJs('ensureMinRows(1); renderQuotationBarcode(); $("#add-item-top").on("click", function(){ $("#add-item").trigger("click"); }); $("#quotation-code-input").on("input", renderQuotationBarcode);');
?>