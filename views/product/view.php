<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use app\models\Inventory;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Product $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

// Check if product has inventory records
$inventoryCount = $model->getInventories()->count();
$isProductUsed = $inventoryCount > 0;

/** @var app\components\Utils $utils */
$utils = Yii::$app->utils;

echo \app\widgets\Modal::widget([
  'id' => 'modal-product',
  'size' => 'modal-md',
]);
?>

<div class="product-view">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header border-bottom-dashed d-flex justify-content-between align-items-center py-3">
          <div>
            <h5 class="card-title mb-0 fw-bold"><?= Html::encode(
                                                  $this->title,
                                                ) ?></h5>
            <small class="text-muted">SKU: <span class="fw-semibold"><?= Html::encode(
                                                                        $model->sku,
                                                                      ) ?></span></small>
          </div>
          <div class="d-flex gap-2">
            <?= Html::button(
              '<i class="ri-edit-line me-1"></i> Update',
              [
                'class' => 'btn btn-primary btn-sm',
                'data' => [
                  'bs-toggle' => 'modal',
                  'bs-target' => '#modal-product',
                  'title' => 'Update Item : ' . $model->name,
                  'url' => Url::to(['update', 'id' => $model->id]),
                ],
              ],
            ) ?>
            <?php if ($isProductUsed): ?>
              <?= Html::button(
                '<i class="ri-delete-bin-line me-1"></i> Delete',
                [
                  'class' => 'btn btn-danger btn-sm disabled',
                  'title' =>
                  'Product cannot be deleted because it has been used in ' .
                    $inventoryCount .
                    ' transaction(s)',
                  'data-bs-toggle' => 'tooltip',
                ],
              ) ?>
            <?php else: ?>
              <?= Html::a(
                '<i class="ri-delete-bin-line me-1"></i> Delete',
                ['delete', 'id' => $model->id],
                [
                  'class' => 'btn btn-danger btn-sm',
                  'data' => [
                    'confirm' =>
                    'Are you sure you want to delete this item?',
                    'method' => 'post',
                  ],
                ],
              ) ?>
            <?php endif; ?>
          </div>
        </div>

        <div class="card-body p-4">
          <div class="row g-4">
            <!-- Left: Image & Stock Summary -->
            <div class="col-lg-3">
              <!-- Product Image -->
              <div class="text-center mb-4">
                <?= Html::img($model->getImagePath(), [
                  'class' =>
                  'img-fluid rounded border border-light shadow-sm',
                  'style' =>
                  'max-height: 280px; object-fit: contain;',
                  'alt' => $model->name,
                ]) ?>
              </div>
            </div>
            <div class="col-lg-3">
              <!-- Stock Summary Box -->
              <div class="card bg-light-subtle border-0 mb-3">
                <div class="card-body text-center">
                  <h6 class="text-muted text-uppercase mb-2 fw-semibold">Current Stock</h6>
                  <h2 class="fw-bold text-primary mb-0"><?= number_format(
                                                          $model->available,
                                                        ) ?></h2>
                  <small class="text-muted">Units Available</small>
                </div>
              </div>

              <!-- Stock Valuation Box -->
              <div class="card bg-danger-subtle border-0">
                <div class="card-body text-center">
                  <h6 class="text-muted text-uppercase mb-2 fw-semibold">Cost Value</h6>
                  <h3 class="fw-bold text-danger mb-0">
                    <?= $utils->dollarFormat(
                      $model->getTotalCostValue()
                    ) ?>
                  </h3>
                  <small class="text-muted">@ Cost Price</small>
                </div>
              </div>
              <div class="card bg-info-subtle border-0">
                <div class="card-body text-center">
                  <h6 class="text-muted text-uppercase mb-2 fw-semibold">Sale Value</h6>
                  <h3 class="fw-bold text-info mb-0">
                    <?= $utils->dollarFormat(
                      $model->available * $model->price,
                    ) ?>
                  </h3>
                  <small class="text-muted">@ Sale Price</small>
                </div>
              </div>
            </div>
            <!-- Right: Product Details -->
            <div class="col-lg-6">
              <!-- Product Information Table -->
              <div class="table-responsive">
                <table class="table table-borderless table-sm">
                  <tbody>
                    <tr>
                      <td class="fw-semibold text-muted" style="width: 30%;">Product Name</td>
                      <td><?= Html::encode(
                            $model->name,
                          ) ?></td>
                    </tr>
                    <tr class="border-bottom">
                      <td class="fw-semibold text-muted">SKU</td>
                      <td><?= Html::encode(
                            $model->sku,
                          ) ?></td>
                    </tr>
                    <tr>
                      <td class="fw-semibold text-muted">Category</td>
                      <td><?= $model->category
                            ? Html::encode(
                              $model->category->name,
                            )
                            : '<span class="text-muted">(not set)</span>' ?></td>
                    </tr>
                    <tr class="border-bottom">
                      <td class="fw-semibold text-muted">Brand</td>
                      <td><?= $model->brand
                            ? Html::encode(
                              $model->brand->name,
                            )
                            : '<span class="text-muted">(not set)</span>' ?></td>
                    </tr>
                    <tr class="border-bottom">
                      <td class="fw-semibold text-muted">Description</td>
                      <td><?= nl2br(
                            Html::encode($model->description),
                          ) ?></td>
                    </tr>
                    <tr>
                      <td class="fw-semibold text-muted">Status</td>
                      <td> <?= $model->getStatusBadge() ?></td>
                    </tr>
                    <tr>
                      <td class="fw-semibold text-muted">Cost Price</td>
                      <td><span class="text-danger fw-bold"><?= Yii::$app->formatter->asCurrency(
                                                              $model->cost,
                                                              'USD',
                                                            ) ?></span></td>
                    </tr>
                    <tr class="border-bottom">
                      <td class="fw-semibold text-muted">Sale Price</td>
                      <td><span class="text-success fw-bold"><?= Yii::$app->formatter->asCurrency(
                                                                $model->price,
                                                                'USD',
                                                              ) ?></span></td>
                    </tr>
                    <tr>
                      <td class="fw-semibold text-muted">Margin</td>
                      <td>
                        <?php
                        $margin =
                          $model->price - $model->cost;
                        $marginPercent =
                          $model->cost > 0
                          ? ($margin / $model->cost) *
                          100
                          : 0;
                        ?>
                        <span class="fw-bold"><?= Yii::$app->formatter->asCurrency(
                                                $margin,
                                                'USD',
                                              ) ?></span>
                        <span class="text-primary">(<?= number_format(
                                                      $marginPercent,
                                                      2,
                                                    ) ?>%)</span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header border-bottom-dashed d-flex justify-content-between align-items-center py-3">
          <h5 class="card-title mb-0 fw-bold">
            <i class="ri-shopping-cart-line me-2"></i> Purchase Order Form
          </h5>
        </div>
        <div class="card-body">
          <?php
          $formOptions = [
            'id' => 'purchase-order-form',
            'action' => Url::to(['purchase-order/create']),
          ];
          $form = ActiveForm::begin($formOptions);
          ?>
          <div class="row">
            <div class="col-md-2">
              <?= $form->field($modelPO, 'code')->textInput(['readonly' => true]) ?>
            </div>
            <div class="col-md-3">
              <?= $form->field($modelPO, 'supplier_id')->dropDownList($suppliers, [
                'prompt' => 'Select Supplier',
                'class' => 'form-control has-select2',
              ]) ?>
            </div>
            <div class="col-md-2">
              <?= $form->field($modelPO, 'date')->textInput([
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
                      <th style="width: 35%">Serial</th>
                      <th style="width: 10%">Qty</th>
                      <th style="width: 15%">Price</th>
                      <th style="width: 15%">Discount</th>
                      <th style="width: 15%">Total</th>
                      <th style="width: 10%"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr data-index="0">
                      <td>
                        <input type="hidden" name="PurchaseOrderItem[0][product_id]" value="<?= $model->id ?>">
                        <input type="hidden" name="PurchaseOrderItem[0][serial]" class="actual-serial">
                        <div class="serial-container">
                          <div class="input-group mb-1 serial-item">
                            <input type="text" class="form-control serial-input" placeholder="Enter Serial...">
                            <button type="button" class="btn btn-outline-info btn-sm generate-serial-sku" title="Generate Serial"><i class="ri-shuffle-line"></i></button>
                            <button type="button" class="btn btn-outline-danger btn-sm remove-serial" style="display:none;"><i class="ri-close-line"></i></button>
                          </div>
                        </div>
                      </td>
                      <td>
                        <input type="number" name="PurchaseOrderItem[0][quantity]" class="form-control qty" value="1" min="1">
                      </td>
                      <td>
                        <input type="number" name="PurchaseOrderItem[0][full_price]" class="form-control full-price" step="0.01" value="<?= $model->cost ?>">
                        <input type="hidden" name="PurchaseOrderItem[0][price]" class="price" value="<?= $model->cost ?>">
                      </td>
                      <td>
                        <div class="input-group">
                          <input type="number" name="PurchaseOrderItem[0][discount]" class="form-control discount" step="0.01" value="0">
                          <button type="button" class="btn btn-outline-secondary toggle-discount-type">$</button>
                          <input type="hidden" name="PurchaseOrderItem[0][discount_type]" class="discount-type" value="fixed">
                        </div>
                      </td>
                      <td class="text-end fw-bold">
                        <span class="item-total">0.00</span>
                      </td>
                      <td></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="row mt-4">
            <div class="col-md-6">
              <?= $form->field($modelPO, 'remark')->textarea(['rows' => 4]) ?>
            </div>
            <div class="col-md-6">
              <div class="card p-3 bg-light">
                <div class="d-flex justify-content-between mb-2">
                  <span class="fw-bold fs-5 text-primary">Sub Total:</span>
                  <span id="sub-total-display" class="fw-bold fs-5 text-primary">0.00</span>
                </div>
                <?= $form
                  ->field($modelPO, 'sub_total')
                  ->hiddenInput(['id' => 'sub-total-input'])
                  ->label(false) ?>

                <div class="d-flex justify-content-between mb-2">
                  <span class="fw-bold fs-6">Total Discount:</span>
                  <span id="total-discount-display" class="fw-bold fs-6">0.00</span>
                </div>
                <?= $form
                  ->field($modelPO, 'discount_amount')
                  ->hiddenInput(['id' => 'discount-amount-input'])
                  ->label(false) ?>

                <div class="row mb-2">
                  <div class="col-6">Delivery Fee:</div>
                  <div class="col-6">
                    <?= Html::activeTextInput($modelPO, 'delivery_fee', [
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
                    <?= Html::activeTextInput($modelPO, 'extra_charge', [
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
                  ->field($modelPO, 'grand_total')
                  ->hiddenInput(['id' => 'grand-total-input'])
                  ->label(false) ?>
              </div>
            </div>
          </div>
          <div class="d-flex mt-4 gap-3">
            <?= Html::submitButton('Create Purchase Order', [
              'class' => 'btn btn-success text-uppercase rounded-pill px-5',
            ]) ?>
          </div>
          <?php ActiveForm::end(); ?>
        </div>
        <?php
        $productViewUrl = Url::to(['product/view']);
        $js = <<<JS
// Generate serial in format: 3 digits + 3 uppercase letters + 1 digit + 3 uppercase letters
function generateSerialCode() {
    const digits = '0123456789';
    const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    
    let serial = '';
    let isUnique = false;
    let attempts = 0;
    
    // Keep generating until we get a unique serial (max 10 attempts)
    while (!isUnique && attempts < 10) {
        serial = '';
        for (let i = 0; i < 3; i++) serial += digits.charAt(Math.floor(Math.random() * digits.length));
        for (let i = 0; i < 3; i++) serial += letters.charAt(Math.floor(Math.random() * letters.length));
        serial += digits.charAt(Math.floor(Math.random() * digits.length));
        for (let i = 0; i < 3; i++) serial += letters.charAt(Math.floor(Math.random() * letters.length));
        
        isUnique = isSerialUnique(serial);
        attempts++;
    }
    
    return serial;
}

// Generate serial with SKU + timestamp (YYMMDDHHMMSS)
function generateSerialWithSKU(sku) {
    const now = new Date();
    const yy = String(now.getFullYear()).slice(-2);
    const mm = String(now.getMonth() + 1).padStart(2, '0');
    const dd = String(now.getDate()).padStart(2, '0');
    const hh = String(now.getHours()).padStart(2, '0');
    const min = String(now.getMinutes()).padStart(2, '0');
    const ss = String(now.getSeconds()).padStart(2, '0');
    
    let serial = sku + yy + mm + dd + hh + min + ss;
    
    // If serial exists, add milliseconds for uniqueness
    let attempts = 0;
    while (!isSerialUnique(serial) && attempts < 10) {
        const ms = String(now.getMilliseconds()).padStart(3, '0');
        serial = sku + yy + mm + dd + hh + min + ss + ms.substring(0, 1 + attempts);
        attempts++;
    }
    
    return serial;
}

$(document).on('click', '.generate-serial', function() {
    const row = $(this).closest('tr');
    const input = $(this).closest('.serial-item').find('.serial-input');
    const serial = generateSerialCode();
    input.val(serial).trigger('input');
    validateSerial(input);
    updateQtyFromSerials(row);
});

$(document).on('click', '.generate-serial-sku', function() {
    const row = $(this).closest('tr');
    const input = $(this).closest('.serial-item').find('.serial-input');
    const sku = "$model->sku"; // Get SKU from product model
    const serial = generateSerialWithSKU(sku);
    input.val(serial).trigger('input');
    validateSerial(input);
    updateQtyFromSerials(row);
});

function calculateTotals() {
    let subTotal = 0;
    let totalDiscount = 0;

    $('#items-table tbody tr').each(function() {
        let row = $(this);
        let qty = parseFloat(row.find('.qty').val()) || 0;
        let fullPrice = parseFloat(row.find('.full-price').val()) || 0;
        let discount = parseFloat(row.find('.discount').val()) || 0;
        let discountType = row.find('.discount-type').val();
        
        let lineGross = qty * fullPrice;
        let lineDiscount = 0;

        if (discountType === 'fixed') {
            lineDiscount = discount * qty;
        } else {
            lineDiscount = lineGross * discount / 100;
        }

        let lineNet = lineGross - lineDiscount;
        row.find('.item-total').text(lineNet.toFixed(2));
        
        // Update hidden price (unit price after discount)
        let netUnitPrice = qty > 0 ? (lineNet / qty) : fullPrice;
        row.find('.price').val(netUnitPrice.toFixed(2));

        subTotal += lineGross;
        totalDiscount += lineDiscount;
    });

    $('#sub-total-display').text(subTotal.toFixed(2));
    $('#sub-total-input').val(subTotal.toFixed(2));
    $('#total-discount-display').text(totalDiscount.toFixed(2));
    $('#discount-amount-input').val(totalDiscount.toFixed(2));

    let deliveryFee = parseFloat($('input[name="PurchaseOrder[delivery_fee]"]').val()) || 0;
    let extraCharge = parseFloat($('input[name="PurchaseOrder[extra_charge]"]').val()) || 0;
    
    let grandTotal = subTotal - totalDiscount + deliveryFee + extraCharge;
    $('#grand-total-display').text(grandTotal.toFixed(2));
    $('#grand-total-input').val(grandTotal.toFixed(2));
}

// Check if serial already exists in form
function isSerialUnique(serialValue, excludeElement = null) {
    let isDuplicate = false;
    $('#items-table .serial-input').each(function() {
        if (excludeElement && $(this).is(excludeElement)) {
            return true; // Skip current element
        }
        if ($(this).val().trim() === serialValue.trim() && serialValue.trim() !== '') {
            isDuplicate = true;
            return false; // Break loop
        }
    });
    return !isDuplicate;
}

// Validate serial input
function validateSerial(input) {
    const serialValue = $(input).val().trim();
    const container = $(input).closest('.serial-item');
    
    if (serialValue === '') {
        container.find('.serial-error').remove();
        return true;
    }
    
    if (!isSerialUnique(serialValue, input)) {
        const errorMsg = '<i class="ri-alert-fill"></i> <strong>Duplicate!</strong> Serial "' + serialValue + '" is already used in this order. Please enter a different serial number.';
        
        if (!container.find('.serial-error').length) {
            container.append('<small class="serial-error text-danger d-block mt-1">' + errorMsg + '</small>');
        } else {
            // Update error message with current serial value
            container.find('.serial-error').html(errorMsg);
        }
        $(input).addClass('is-invalid');
        // Select the text for easy correction
        input.select();
        input.focus();
        return false;
    } else {
        container.find('.serial-error').remove();
        $(input).removeClass('is-invalid');
        return true;
    }
}
document.getElementById('purchase-order-form').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
        // Allow Enter only in serial-input fields for adding new ones
        if (!$(e.target).hasClass('serial-input')) {
            e.preventDefault();
            return false;
        }
    }
});

// Serial input logic
$(document).on('keypress', '.serial-input', function(e) {
    if (e.which == 13) {
        e.preventDefault();
        
        // Validate serial before adding new row
        if (!validateSerial(this)) {
            return false;
        }
        
        let row = $(this).closest('tr');
        let container = $(this).closest('.serial-container');
        
        let newItem = $(`
            <div class="input-group mb-1 serial-item">
                <input type="text" class="form-control serial-input" placeholder="Enter Serial...">
                <button type="button" class="btn btn-outline-danger btn-sm remove-serial"><i class="ri-close-line"></i></button>
            </div>
        `);
        container.append(newItem);
        newItem.find('input').focus();
        
        updateQtyFromSerials(row);
        calculateTotals();
    }
});

$(document).on('click', '.remove-serial', function() {
    let row = $(this).closest('tr');
    $(this).closest('.serial-item').remove();
    updateQtyFromSerials(row);
    calculateTotals();
});

function updateQtyFromSerials(row) {
    let container = row.find('.serial-container');
    let qtyInput = row.find('.qty');
    
    // Count only non-empty serials
    let filledSerials = [];
    container.find('.serial-input').each(function() {
        let v = $(this).val().trim();
        if (v) filledSerials.push(v);
    });
    
    let filledCount = filledSerials.length;
    
    if (filledCount > 1) {
        qtyInput.val(filledCount).attr('readonly', true);
        container.find('.remove-serial').show();
    } else {
        qtyInput.removeAttr('readonly');
        container.find('.remove-serial').hide();
    }
    
    // Update hidden serial field with only non-empty serials
    row.find('.actual-serial').val(filledSerials.join(', '));
}

$(document).on('input change blur', '.serial-input', function() {
    validateSerial(this);
    
    let row = $(this).closest('tr');
    updateQtyFromSerials(row);
    calculateTotals();
});

$(document).on('click', '.toggle-discount-type', function() {
    let btn = $(this);
    let input = btn.siblings('.discount-type');
    if (input.val() === 'fixed') {
        input.val('percentage');
        btn.text('%');
    } else {
        input.val('fixed');
        btn.text('$');
    }
    calculateTotals();
});

$(document).on('input change', '.qty, .full-price, .discount, .cost-calc', function() {
    calculateTotals();
});

// Make table rows clickable to navigate to product view
$(document).on('click', 'tbody tr', function(e) {
    if ($(e.target).closest('input, button, .remove-serial, .serial-input, .generate-serial, .generate-serial-sku').length) {
        return;
    }
    
    const productId = $(this).find('input[name*="product_id"]').val();
    if (productId) {
        window.location.href = '$productViewUrl' + '?id=' + productId;
    }
});

// Validate all serials before form submission
$('#purchase-order-form').on('submit', function(e) {
    let hasInvalidSerial = false;
    
    $('#items-table .serial-input').each(function() {
        const serialValue = $(this).val().trim();
        if (serialValue && !isSerialUnique(serialValue, this)) {
            hasInvalidSerial = true;
            validateSerial(this);
        }
    });
    
    if (hasInvalidSerial) {
        e.preventDefault();
        alert('Please fix duplicate serials before submitting');
        return false;
    }
});

calculateTotals();
JS;
        $this->registerJs($js);
        ?>

      </div>
    </div>
  </div>

  <!-- Transaction History -->
  <div class="row">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-header border-bottom-dashed d-flex justify-content-between align-items-center py-3">
          <h5 class="card-title mb-0 fw-bold">
            <i class="ri-history-line me-2"></i> Stock Movement History
          </h5>
          <span class="badge bg-secondary">PO & Invoices</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <?= GridView::widget([
              'dataProvider' => new \yii\data\ActiveDataProvider([
                'query' => $model->getInventories(),
                'pagination' => ['pageSize' => 10],
              ]),
              'tableOptions' => [
                'class' => 'table table-hover table-striped align-middle mb-0',
              ],
              'layout' =>
              "{items}\n<div class='d-flex justify-content-center p-3 border-top'>{pager}</div>",
              'emptyText' =>
              '<div class="p-5 text-center"><i class="ri-inbox-line display-5 text-muted"></i><p class="text-muted mt-2">No inventory transactions found.</p></div>',
              'columns' => [
                [
                  'attribute' => 'created_at',
                  'label' => 'Date & Time',
                  'value' => function ($inv) {
                    return Yii::$app->utils->dateTime(
                      $inv->created_at,
                    );
                  },
                  'headerOptions' => [
                    'class' =>
                    'bg-light fw-semibold text-uppercase text-muted small',
                  ],
                  'contentOptions' => ['class' => 'fw-500'],
                ],
                [
                  'attribute' => 'type',
                  'label' => 'Reference Document',
                  'format' => 'raw',
                  'headerOptions' => [
                    'class' =>
                    'bg-light fw-semibold text-uppercase text-muted small',
                  ],
                  'value' => function ($inv) {
                    $label = $inv->typeLabel;
                    $route = '';
                    $icon = '';
                    $badgeClass = 'bg-light text-dark';

                    if (
                      $inv->type === Inventory::TYPE_PURCHASE_ORDER
                    ) {
                      $route = 'purchase-order/view';
                      $icon =
                        '<i class="ri-shopping-cart-line me-1"></i>';
                      $badgeClass = 'bg-info-subtle text-info';
                    } elseif (
                      $inv->type === Inventory::TYPE_INVOICE
                    ) {
                      $route = 'invoice/view';
                      $icon =
                        '<i class="ri-file-text-line me-1"></i>';
                      $badgeClass =
                        'bg-success-subtle text-success';
                    }

                    $text = $label . ' #' . $inv->transaction_id;
                    if ($route) {
                      return Html::a(
                        $icon . $text,
                        [$route, 'id' => $inv->transaction_id],
                        [
                          'class' =>
                          'text-decoration-none fw-semibold link-primary',
                          'data-pjax' => '0',
                        ],
                      );
                    }
                    return $text;
                  },
                ],
                [
                  'attribute' => 'in',
                  'label' => 'IN (+)',
                  'headerOptions' => [
                    'class' =>
                    'bg-light fw-semibold text-uppercase text-muted small text-center',
                  ],
                  'contentOptions' => [
                    'class' => 'text-center fw-bold',
                  ],
                  'value' => function ($inv) {
                    return $inv->in > 0
                      ? '<span class="text-success fs-6">+' .
                      number_format($inv->in) .
                      '</span>'
                      : '<span class="text-muted">—</span>';
                  },
                  'format' => 'raw',
                ],
                [
                  'attribute' => 'out',
                  'label' => 'OUT (-)',
                  'headerOptions' => [
                    'class' =>
                    'bg-light fw-semibold text-uppercase text-muted small text-center',
                  ],
                  'contentOptions' => [
                    'class' => 'text-center fw-bold',
                  ],
                  'value' => function ($inv) {
                    return $inv->out > 0
                      ? '<span class="text-danger fs-6">-' .
                      number_format($inv->out) .
                      '</span>'
                      : '<span class="text-muted">—</span>';
                  },
                  'format' => 'raw',
                ],
              ],
            ]) ?>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card">
        <div class="card-header border-bottom-dashed d-flex justify-content-between align-items-center py-3">
          <h5 class="card-title mb-0 fw-bold">
            <i class="ri-barcode-line me-2"></i> Available Serials
          </h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-sm table-striped table-hover">
              <thead class="bg-light">
                <tr>
                  <th>Serial Number</th>
                  <th>Added At</th>
                  <th class="text-end">Cost</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $variations = \app\models\ProductVariation::find()
                  ->where(['product_id' => $model->id])
                  ->orderBy(['created_at' => SORT_DESC])
                  ->all();

                if (empty($variations)): ?>
                  <tr>
                    <td colspan="3" class="text-center text-muted py-3">No serials found</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($variations as $variation): ?>
                    <tr>
                      <td class="fw-medium"><?= Html::encode($variation->serial) ?></td>
                      <td class="fw-medium text-muted"><?= $utils->dateTime($variation->created_at) ?></td>
                      <td class="text-end fw-bold text-danger"><?= Html::encode(number_format($variation->cost, 2)) ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>