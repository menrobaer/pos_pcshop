<?php

/** @var yii\web\View $this */
/** @var app\models\User $user */
/** @var int $totalInvoices */
/** @var float $totalRevenue */
/** @var int $totalPOs */
/** @var float $totalPOAmount */
/** @var int $totalCustomers */
/** @var int $totalProducts */
/** @var int $totalStock */
/** @var array $recentInvoices */
/** @var array $recentPOs */
/** @var array $topProducts */
/** @var array $bestSellers */
/** @var array $recentActivities */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashboard';
$user = Yii::$app->user->identity;

// Dynamic statistics from database
$stats = [
  'total_revenue' => $totalRevenue,
  'total_invoices' => $totalInvoices,
  'total_po_amount' => $totalPOAmount,
  'total_pos' => $totalPOs,
  'total_customers' => $totalCustomers,
  'total_products' => $totalProducts,
  'total_stock' => $totalStock,
];

$recentOrders = [];
foreach ($recentInvoices as $invoice) {
  $recentOrders[] = [
    'id' => $invoice->id,
    'code' => $invoice->code,
    'customer' => $invoice->customer ? $invoice->customer->name : 'N/A',
    'amount' => $invoice->grand_total,
    'status' => $invoice->getStatusLabel(),
    'date' => $invoice->date,
  ];
}

$topSellers = [];
foreach ($topProducts as $product) {
  $topSellers[] = [
    'id' => $product->id,
    'name' => $product->name,
    'category' => $product->category ? $product->category->name : 'N/A',
    'stock' => $product->available,
    'price' => $product->price,
  ];
}

$formatActivityReference = function ($activity) {
  $params = $activity->params ? json_decode($activity->params, true) : [];
  if (!is_array($params)) {
    $params = [];
  }
  $flatten = [];
  foreach ($params as $key => $value) {
    if (is_array($value)) {
      foreach ($value as $nestedKey => $nestedValue) {
        if (!is_array($nestedValue) && $nestedValue !== null) {
          $flatten[$nestedKey] = $nestedValue;
        }
      }
    } elseif ($value !== null) {
      $flatten[$key] = $value;
    }
  }
  $pieces = [];

  // For payment actions, show invoice or PO code and amount
  if (in_array($activity->action, ['payment', 'add-payment'])) {
    if (!empty($flatten['invoice_code'])) {
      $pieces[] = 'Invoice: ' . $flatten['invoice_code'];
    } elseif (!empty($flatten['purchase_order_code'])) {
      $pieces[] = 'PO: ' . $flatten['purchase_order_code'];
    }
    if (!empty($flatten['amount'])) {
      $pieces[] = 'Amount: $' . $flatten['amount'];
    }
  } else {
    // Standard reference formatting for other actions
    foreach (['file', 'code', 'name', 'title', 'serial'] as $key) {
      if (!empty($flatten[$key])) {
        $pieces[] = ucfirst($key) . ': ' . $flatten[$key];
      }
    }
  }

  if (empty($pieces) && !empty($flatten['id'])) {
    $pieces[] = 'ID: ' . $flatten['id'];
  }
  return $pieces ? implode(' | ', $pieces) : '-';
};

$activityViewUrl = function ($activity) {
  $params = $activity->params ? json_decode($activity->params, true) : [];
  if (!is_array($params)) {
    $params = [];
  }
  $flatten = [];
  foreach ($params as $key => $value) {
    if (is_array($value)) {
      foreach ($value as $nestedKey => $nestedValue) {
        if (!is_array($nestedValue) && $nestedValue !== null) {
          $flatten[$nestedKey] = $nestedValue;
        }
      }
    } elseif ($value !== null) {
      $flatten[$key] = $value;
    }
  }

  $id = null;
  // For payment actions, look for invoice_id or purchase_order_id
  if (in_array($activity->action, ['payment', 'add-payment'])) {
    if (!empty($flatten['invoice_id'])) {
      return Url::to(['invoice/view', 'id' => $flatten['invoice_id']]);
    } elseif (!empty($flatten['purchase_order_id'])) {
      return Url::to([
        'purchase-order/view',
        'id' => $flatten['purchase_order_id'],
      ]);
    }
  }

  // For other actions, use the standard ID lookup
  if (isset($flatten['id'])) {
    $id = $flatten['id'];
  }
  foreach ($params as $value) {
    if (is_array($value) && isset($value['id'])) {
      $id = $value['id'];
      break;
    }
  }
  if ($id && $activity->controller) {
    return Url::to([$activity->controller . '/view', 'id' => $id]);
  }
  return null;
};
?>
<div class="row">
    <div class="col">

        <div class="h-100">
            <div class="row mb-3 pb-1">
                <div class="col-12">
                    <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                        <div class="flex-grow-1">
                            <h4 class="fs-16 mb-1">Good Morning, <?= $user
                              ? $user->first_name
                              : 'Guest' ?>!</h4>
                            <p class="text-muted mb-0">Here's what's happening with your store today.</p>
                        </div>
                        <div class="mt-3 mt-lg-0">
                            <form method="get">
                                <div class="row g-3 mb-0 align-items-center">
                                    <div class="col-sm-auto">
                                        <div class="input-group">
                                            <input type="text" name="date_range" class="form-control border-0 dash-filter-picker shadow" data-provider="flatpickr" data-range-date="true" data-date-format="d M, Y" value="<?= Html::encode(
                                              Yii::$app->request->get(
                                                'date_range',
                                                date('01 M, Y') .
                                                  ' to ' .
                                                  date('d M, Y'),
                                              ),
                                            ) ?>">
                                            <button type="submit" class="input-group-text bg-primary border-primary text-white">
                                                <i class="ri-equalizer-fill"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div><!-- end card header -->
                </div>
                <!--end col-->
            </div>
            <!--end row-->

            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <!-- card -->
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Revenue</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <h5 class="text-success fs-14 mb-0">
                                        <i class="ri-money-dollar-circle-line fs-13 align-middle"></i>
                                    </h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">$<span class="counter-value" data-target="<?= (float) $stats[
                                      'total_revenue'
                                    ] ?>">0</span></h4>
                                    <a href="<?= Url::to([
                                      'invoice/index',
                                    ]) ?>" class="text-decoration-underline">View invoices</a>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-success-subtle rounded fs-3">
                                        <i class="ri-money-dollar-circle-line text-success"></i>
                                    </span>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div><!-- end col -->

                <div class="col-xl-3 col-md-6">
                    <!-- card -->
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Invoices</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <h5 class="text-info fs-14 mb-0">
                                        <i class="ri-file-text-line fs-13 align-middle"></i>
                                    </h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value" data-target="<?= $stats[
                                      'total_invoices'
                                    ] ?>">0</span></h4>
                                    <a href="<?= Url::to([
                                      'invoice/index',
                                    ]) ?>" class="text-decoration-underline">View all invoices</a>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-info-subtle rounded fs-3">
                                        <i class="ri-file-text-line text-info"></i>
                                    </span>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div><!-- end col -->

                <div class="col-xl-3 col-md-6">
                    <!-- card -->
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Customers</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <h5 class="text-warning fs-14 mb-0">
                                        <i class="ri-user-3-line fs-13 align-middle"></i>
                                    </h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value" data-target="<?= $stats[
                                      'total_customers'
                                    ] ?>">0</span></h4>
                                    <a href="<?= Url::to([
                                      'customer/index',
                                    ]) ?>" class="text-decoration-underline">See details</a>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-warning-subtle rounded fs-3">
                                        <i class="ri-user-3-line text-warning"></i>
                                    </span>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div><!-- end col -->

                <div class="col-xl-3 col-md-6">
                    <!-- card -->
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total In Stock</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <h5 class="text-primary fs-14 mb-0">
                                        <i class="ri-apps-2-line fs-13 align-middle"></i>
                                    </h5>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-4">
                                <div>
                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value" data-target="<?= $stats[
                                      'total_stock'
                                    ] ?>">0</span></h4>
                                    <a href="<?= Url::to([
                                      'product/index',
                                    ]) ?>" class="text-decoration-underline">View products</a>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-primary-subtle rounded fs-3">
                                        <i class="ri-apps-2-line text-primary"></i>
                                    </span>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div><!-- end col -->
            </div>

            <!-- Recent Invoices and Top Products Section -->
            <div class="row">
                <div class="col-xl-6">
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Best Selling Products</h4>
                            <div class="flex-shrink-0">
                                <span class="badge bg-success-subtle text-success">By Quantity</span>
                            </div>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <div class="table-responsive table-card">
                                <table class="table table-centered table-hover align-middle table-nowrap mb-0">
                                    <thead class="text-muted table-light">
                                        <tr>
                                            <th scope="col">Product</th>
                                            <th scope="col" class="text-end">Sold Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($bestSellers)): ?>
                                            <tr>
                                                <td colspan="2" class="text-center text-muted py-4">No data available</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach (
                                              $bestSellers
                                              as $seller
                                            ): ?>
                                                <tr>
                                                    <td>
                                                        <h5 class="fs-14 my-1">
                                                            <a href="<?= Url::to(
                                                              [
                                                                'product/view',
                                                                'id' =>
                                                                  $seller[
                                                                    'product_id'
                                                                  ],
                                                              ],
                                                            ) ?>" class="text-reset">
                                                                <?= Html::encode(
                                                                  $seller[
                                                                    'product_name'
                                                                  ],
                                                                ) ?>
                                                            </a>
                                                        </h5>
                                                    </td>
                                                    <td class="text-end">
                                                        <span class="badge bg-primary-subtle text-primary fs-12"><?= number_format(
                                                          $seller['qty'],
                                                        ) ?></span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div> <!-- .card-body-->
                    </div> <!-- .card-->
                </div> <!-- .col-->

                <div class="col-xl-6">
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Top Products (By Stock)</h4>
                            <a href="<?= Url::to([
                              'product/index',
                            ]) ?>" class="text-decoration-underline">View all</a>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <div class="table-responsive table-card">
                                <table class="table table-centered table-hover align-middle table-nowrap mb-0">
                                    <tbody>
                                        <?php if (empty($topSellers)): ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">No products found</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach (
                                              $topSellers
                                              as $product
                                            ): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div>
                                                                <h5 class="fs-14 my-1">
                                                                    <a href="<?= Url::to(
                                                                      [
                                                                        'product/view',
                                                                        'id' =>
                                                                          $product[
                                                                            'id'
                                                                          ],
                                                                      ],
                                                                    ) ?>" class="text-reset">
                                                                        <?= Html::encode(
                                                                          $product[
                                                                            'name'
                                                                          ],
                                                                        ) ?>
                                                                    </a>
                                                                </h5>
                                                                <span class="text-muted"><?= Html::encode(
                                                                  $product[
                                                                    'category'
                                                                  ],
                                                                ) ?></span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-muted text-end">Stock</div>
                                                        <h5 class="fs-14 my-1 fw-normal text-end"><?= number_format(
                                                          $product['stock'],
                                                        ) ?></h5>
                                                    </td>
                                                    <td>
                                                        <div class="text-muted text-end">Price</div>
                                                        <h5 class="fs-14 my-1 fw-normal text-end">$<?= number_format(
                                                          $product['price'],
                                                          2,
                                                        ) ?></h5>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div> <!-- .card-body-->
                    </div> <!-- .card-->
                </div> <!-- .col-->
            </div> <!-- .row-->

            <div class="row">
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Recent Invoices</h4>
                            <a href="<?= Url::to([
                              'invoice/index',
                            ]) ?>" class="text-decoration-underline">View all</a>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <div class="table-responsive table-card">
                                <table class="table table-borderless table-centered align-middle table-nowrap mb-0">
                                    <thead class="text-muted table-light">
                                        <tr>
                                            <th scope="col">Invoice #</th>
                                            <th scope="col">Customer</th>
                                            <th scope="col">Amount</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($recentOrders)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">No invoices found</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach (
                                              $recentOrders
                                              as $order
                                            ): ?>
                                                <tr>
                                                    <td>
                                                        <a href="<?= Url::to([
                                                          'invoice/view',
                                                          'id' => $order['id'],
                                                        ]) ?>" class="fw-medium link-primary">
                                                            <?= Html::encode(
                                                              $order['code'],
                                                            ) ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-grow-1"><?= Html::encode(
                                                              $order[
                                                                'customer'
                                                              ],
                                                            ) ?></div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="text-success">$<?= number_format(
                                                          $order['amount'],
                                                          2,
                                                        ) ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?= strpos(
                                                          $order['status'],
                                                          'Paid',
                                                        ) !== false
                                                          ? 'bg-success-subtle text-success'
                                                          : (strpos(
                                                            $order['status'],
                                                            'Pending',
                                                          ) !== false
                                                            ? 'bg-warning-subtle text-warning'
                                                            : 'bg-danger-subtle text-danger') ?>">
                                                            <?= Html::encode(
                                                              $order['status'],
                                                            ) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= \app\components\Utils::date(
                                                      $order['date'],
                                                    ) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Recent Purchase Orders</h4>
                            <a href="<?= Url::to([
                              'purchase-order/index',
                            ]) ?>" class="text-decoration-underline">View all</a>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <div class="table-responsive table-card">
                                <table class="table table-borderless table-centered align-middle table-nowrap mb-0">
                                    <thead class="text-muted table-light">
                                        <tr>
                                            <th scope="col">PO #</th>
                                            <th scope="col">Supplier</th>
                                            <th scope="col">Amount</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($recentPOs)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">No purchase orders found</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach (
                                              $recentPOs
                                              as $po
                                            ): ?>
                                                <tr>
                                                    <td>
                                                        <a href="<?= Url::to([
                                                          'purchase-order/view',
                                                          'id' => $po->id,
                                                        ]) ?>" class="fw-medium link-primary">
                                                            <?= Html::encode(
                                                              $po->code,
                                                            ) ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-grow-1"><?= Html::encode(
                                                              $po->supplier
                                                                ? $po->supplier
                                                                  ->name
                                                                : 'N/A',
                                                            ) ?></div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="text-success">$<?= number_format(
                                                          $po->grand_total,
                                                          2,
                                                        ) ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?= strpos(
                                                          $po->getStatusLabel(),
                                                          'Paid',
                                                        ) !== false
                                                          ? 'bg-success-subtle text-success'
                                                          : (strpos(
                                                            $po->getStatusLabel(),
                                                            'Pending',
                                                          ) !== false
                                                            ? 'bg-warning-subtle text-warning'
                                                            : 'bg-danger-subtle text-danger') ?>">
                                                            <?= Html::encode(
                                                              $po->getStatusLabel(),
                                                            ) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= \app\components\Utils::date(
                                                      $po->date,
                                                    ) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- end row -->

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Recent Activity</h4>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <div class="table-responsive table-card">
                                <table class="table table-borderless table-centered align-middle table-nowrap mb-0">
                                    <thead class="text-muted table-light">
                                        <tr>
                                            <th scope="col">User</th>
                                            <th scope="col">Controller/Action</th>
                                            <th scope="col">Method</th>
                                            <th scope="col">Reference</th>
                                            <th scope="col">IP Address</th>
                                            <th scope="col">Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($recentActivities)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">No activity found</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach (
                                              $recentActivities
                                              as $activity
                                            ): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-grow-1">
                                                                <?= $activity->user
                                                                  ? Html::encode(
                                                                    $activity
                                                                      ->user
                                                                      ->first_name .
                                                                      ' ' .
                                                                      $activity
                                                                        ->user
                                                                        ->last_name,
                                                                  )
                                                                  : '<span class="text-muted">System/Guest</span>' ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="text-primary"><?= Html::encode(
                                                          ucfirst(
                                                            $activity->controller,
                                                          ),
                                                        ) ?></span> / 
                                                        <span class="text-muted"><?= Html::encode(
                                                          $activity->action,
                                                        ) ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info-subtle text-info"><?= Html::encode(
                                                          $activity->method,
                                                        ) ?></span>
                                                    </td>
                                                    <?php
                                                    $reference = $formatActivityReference(
                                                      $activity,
                                                    );
                                                    $viewUrl = $activityViewUrl(
                                                      $activity,
                                                    );
                                                    ?>
                                                    <td>
                                                        <?php if (
                                                          $viewUrl !== null
                                                        ): ?>
                                                            <?= Html::a(
                                                              Html::encode(
                                                                $reference,
                                                              ),
                                                              $viewUrl,
                                                              [
                                                                'class' =>
                                                                  'text-decoration-underline',
                                                              ],
                                                            ) ?>
                                                        <?php else: ?>
                                                            <?= Html::encode(
                                                              $reference,
                                                            ) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= Html::encode(
                                                      $activity->ip_address,
                                                    ) ?></td>
                                                    <td><?= Yii::$app->utils->dateTime(
                                                      $activity->created_at,
                                                    ) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table><!-- end table -->
                            </div>
                        </div>
                    </div> <!-- .card-->
                </div> <!-- .col-->
            </div> <!-- end row -->

        </div> <!-- end .h-100-->

    </div>
