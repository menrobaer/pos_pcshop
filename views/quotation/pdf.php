<?php
use yii\helpers\Html;
/** @var yii\web\View $this */
/** @var app\models\Quotation $model */
?>
<style>
  /* ensure mPDF uses the registered khmeros font for Khmer */
  body{font-family: khmeros, DejaVu Sans, sans-serif;}
</style>
<div class="quotation-view">
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <?php if ($outlet && $outlet->image): ?>
                    <?= Html::img($outlet->getImagePath(), [
                      'style' => 'max-height: 80px; margin-bottom: 10px;',
                      'alt' => 'Logo',
                    ]) ?>
                <?php endif; ?>
                <h3 style="margin: 0; padding: 0; font-weight: bold; font-size: 24px;">QUOTATION</h3>
                
                <div style="margin-top: 20px;">
                    <h5 style="margin: 0 0 5px 0; color: #6c757d; text-transform: uppercase; font-size: 10px;">From Address</h5>
                    <p style="margin: 0 0 5px 0; font-weight: bold; font-size: 14px;"><?= $outlet
                      ? Html::encode($outlet->name)
                      : 'POS VLC Company' ?></p>
                    <p style="margin: 0; color: #6c757d; font-size: 12px;"><?= $outlet
                      ? nl2br(Html::encode($outlet->address))
                      : '123 Business Avenue, Suite 456' ?></p>
                    <p style="margin: 5px 0 0 0; color: #6c757d; font-size: 12px;">Phone: <?= $outlet
                      ? Html::encode($outlet->phone)
                      : '+(01) 234 6789' ?></p>
                </div>
            </td>
            <td style="width: 40%; vertical-align: top; text-align: right;">
                <p style="margin: 0 0 5px 0; font-size: 12px;"><span style="color: #6c757d;">Email:</span> <?= $outlet
                  ? Html::encode($outlet->email)
                  : 'support@posvlc.com' ?></p>
                <p style="margin: 0; font-size: 12px;"><span style="color: #6c757d;">Website:</span> <?= $outlet
                  ? Html::encode($outlet->website)
                  : 'www.posvlc.com' ?></p>
            </td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td style="width: 25%; vertical-align: top;">
                <p style="margin: 0 0 5px 0; color: #6c757d; text-transform: uppercase; font-size: 10px; font-weight: bold;">Quotation No</p>
                <h5 style="margin: 0; font-size: 14px;">#<?= Html::encode(
                  $model->code,
                ) ?></h5>
            </td>
            <td style="width: 25%; vertical-align: top;">
                <p style="margin: 0 0 5px 0; color: #6c757d; text-transform: uppercase; font-size: 10px; font-weight: bold;">Date</p>
                <h5 style="margin: 0; font-size: 14px;"><?= date(
                  'd M, Y',
                  strtotime($model->date),
                ) ?></h5>
            </td>
            <td style="width: 25%; vertical-align: top;">
                <p style="margin: 0 0 5px 0; color: #6c757d; text-transform: uppercase; font-size: 10px; font-weight: bold;">Status</p>
                <?= $model->getStatusLabel() ?>
            </td>
            <td style="width: 25%; vertical-align: top;">
                <p style="margin: 0 0 5px 0; color: #6c757d; text-transform: uppercase; font-size: 10px; font-weight: bold;">Total Amount</p>
                <h5 style="margin: 0; font-size: 14px;">$<?= number_format(
                  $model->grand_total,
                  2,
                ) ?></h5>
            </td>
        </tr>
    </table>

    <div style="border-top: 1px dashed #e9ebec; padding-top: 20px; margin-bottom: 20px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <h6 style="margin: 0 0 10px 0; color: #6c757d; text-transform: uppercase; font-size: 11px; font-weight: bold;">Customer Details</h6>
                    <p style="margin: 0 0 5px 0; font-weight: bold; font-size: 13px;"><?= $model->customer
                      ? Html::encode($model->customer->name)
                      : '-' ?></p>
                    <p style="margin: 0 0 5px 0; color: #6c757d; font-size: 12px;"><?= $model->customer
                      ? Html::encode($model->customer->address)
                      : '-' ?></p>
                    <p style="margin: 0; color: #6c757d; font-size: 12px;">Phone: <?= $model->customer
                      ? Html::encode($model->customer->phone)
                      : '-' ?></p>
                </td>
                <td style="width: 40%; vertical-align: top; text-align: right;">
                    <h6 style="margin: 0 0 10px 0; color: #6c757d; text-transform: uppercase; font-size: 11px; font-weight: bold;">Due Date</h6>
                    <h5 style="margin: 0; font-size: 14px; color: #dc3545;"><?= date(
                      'd M, Y',
                      strtotime($model->due_date),
                    ) ?></h5>
                </td>
            </tr>
        </table>
    </div>

    <table class="table" style="width: 100%; border-collapse: collapse; margin-bottom: 0;">
        <thead>
            <tr style="background-color: #f3f6f9;">
                <th style="padding: 10px; text-align: center; font-size: 12px; font-weight: bold; border-bottom: 1px solid #e9ebec; width: 50px;">#</th>
                <th style="padding: 10px; text-align: left; font-size: 12px; font-weight: bold; border-bottom: 1px solid #e9ebec;">Product Details</th>
                <th style="padding: 10px; text-align: center; font-size: 12px; font-weight: bold; border-bottom: 1px solid #e9ebec; width: 100px;">Serial</th>
                <th style="padding: 10px; text-align: center; font-size: 12px; font-weight: bold; border-bottom: 1px solid #e9ebec; width: 80px;">Price</th>
                <th style="padding: 10px; text-align: center; font-size: 12px; font-weight: bold; border-bottom: 1px solid #e9ebec; width: 80px;">Qty</th>
                <th style="padding: 10px; text-align: right; font-size: 12px; font-weight: bold; border-bottom: 1px solid #e9ebec; width: 100px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($model->items as $index => $item): ?>
            <tr>
                <td style="padding: 10px; text-align: center; font-size: 12px; border-bottom: 1px solid #e9ebec; vertical-align: top;">
                    <?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?>
                </td>
                <td style="padding: 10px; text-align: left; font-size: 12px; border-bottom: 1px solid #e9ebec; vertical-align: top;">
                    <span style="font-weight: bold; display: block; margin-bottom: 5px;"><?= Html::encode(
                      $item->product_name,
                    ) ?></span>
                    
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <?php if (
                              $item->product &&
                              $item->product->image
                            ): ?>
                            <td style="width: 50px; vertical-align: top; padding-right: 10px;">
                                <?= Html::img($item->product->getImagePath(), [
                                  'style' =>
                                    'width: 40px; height: 40px; object-fit: contain; border: 1px solid #dee2e6; padding: 2px; border-radius: 4px;',
                                ]) ?>
                            </td>
                            <?php endif; ?>
                            <td style="vertical-align: top;">
                                <?php if ($item->sku): ?>
                                    <p style="margin: 0 0 2px 0; color: #6c757d; font-size: 11px;"><span style="font-weight: 500;">SKU:</span> <?= Html::encode(
                                      $item->sku,
                                    ) ?></p>
                                <?php endif; ?>
                                <?php if ($item->description): ?>
                                    <p style="margin: 0; color: #6c757d; font-size: 11px;"><?= nl2br(
                                      Html::encode($item->description),
                                    ) ?></p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="padding: 10px; text-align: center; font-size: 12px; border-bottom: 1px solid #e9ebec; vertical-align: top;">
                    <?= Html::encode($item->serial ?: '-') ?>
                </td>
                <td style="padding: 10px; text-align: center; font-size: 12px; border-bottom: 1px solid #e9ebec; vertical-align: top;">
                    $<?= number_format($item->full_price, 2) ?>
                    <?php if ($item->discount > 0): ?>
                        <div style="color: #dc3545; font-size: 10px; margin-top: 2px;">
                            - <?= $item->discount_type === 'percentage'
                              ? number_format($item->discount, 0) . '%'
                              : '$' . number_format($item->discount, 2) ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td style="padding: 10px; text-align: center; font-size: 12px; border-bottom: 1px solid #e9ebec; vertical-align: top;">
                    <?= $item->quantity ?>
                </td>
                <td style="padding: 10px; text-align: right; font-size: 12px; border-bottom: 1px solid #e9ebec; vertical-align: top;">
                    <?php
                    $lineGross = $item->quantity * $item->full_price;
                    $lineNet = $item->quantity * $item->price;
                    ?>
                    <div style="font-weight: bold;">$<?= number_format(
                      $lineNet,
                      2,
                    ) ?></div>
                    <?php if ($lineGross > $lineNet): ?>
                        <div style="color: #6c757d; text-decoration: line-through; font-size: 10px;">$<?= number_format(
                          $lineGross,
                          2,
                        ) ?></div>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <table style="width: 100%; margin-top: 10px; border-collapse: collapse;">
        <tr>
            <td style="vertical-align: top;"></td>
            <td style="width: 250px; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 5px 0; font-size: 12px;">Sub Total</td>
                        <td style="padding: 5px 0; text-align: right; font-size: 12px;">$<?= number_format(
                          $model->sub_total,
                          2,
                        ) ?></td>
                    </tr>
                    <?php if ($model->discount_amount > 0): ?>
                    <tr>
                        <td style="padding: 5px 0; font-size: 12px;">Discount</td>
                        <td style="padding: 5px 0; text-align: right; font-size: 12px; color: #dc3545;">- $<?= number_format(
                          $model->discount_amount,
                          2,
                        ) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($model->delivery_fee > 0): ?>
                    <tr>
                        <td style="padding: 5px 0; font-size: 12px;">Delivery Fee</td>
                        <td style="padding: 5px 0; text-align: right; font-size: 12px;">$<?= number_format(
                          $model->delivery_fee,
                          2,
                        ) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($model->extra_charge > 0): ?>
                    <tr>
                        <td style="padding: 5px 0; font-size: 12px;">Extra Charge</td>
                        <td style="padding: 5px 0; text-align: right; font-size: 12px;">$<?= number_format(
                          $model->extra_charge,
                          2,
                        ) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td style="padding: 10px 0; font-size: 14px; font-weight: bold; border-top: 1px dashed #e9ebec;">Total Amount</td>
                        <td style="padding: 10px 0; text-align: right; font-size: 16px; font-weight: bold; color: #0ab39c; border-top: 1px dashed #e9ebec;">$<?= number_format(
                          $model->grand_total,
                          2,
                        ) ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <?php if ($model->remark): ?>
    <div style="margin-top: 20px; background-color: #eff2f7; border: 1px solid #b6effb; padding: 15px; border-radius: 4px;">
        <p style="margin: 0; font-size: 12px; color: #299cdb;"><span style="font-weight: bold; margin-right: 5px;">NOTES:</span>
            <?= nl2br(Html::encode($model->remark)) ?>
        </p>
    </div>
    <?php endif; ?>

    <?php if ($outlet && $outlet->terms): ?>
    <div style="margin-top: 20px;">
        <h6 style="margin: 0 0 10px 0; color: #6c757d; text-transform: uppercase; font-size: 11px; font-weight: bold;">Terms & Conditions:</h6>
        <p style="margin: 0; color: #6c757d; font-size: 12px;"><?= nl2br(
          Html::encode($outlet->terms),
        ) ?></p>
    </div>
    <?php endif; ?>
</div>
