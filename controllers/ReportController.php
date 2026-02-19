<?php

namespace app\controllers;

use app\models\Expense;
use app\models\Invoice;
use app\models\InvoiceItem;
use app\models\Inventory;
use app\models\PurchaseOrder;
use DateInterval;
use DatePeriod;
use DateTime;
use Yii;
use yii\db\Expression;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

class ReportController extends Controller
{

  public function behaviors()
  {
    return array_merge(
      parent::behaviors(),
      [
        'access' => [
          'class' => \yii\filters\AccessControl::class,
          'rules' => [
            [
              'actions' => \app\models\User::getUserPermission(Yii::$app->controller->id),
              'allow' => true,
            ]
          ],
        ],
        'verbs' => [
          'class' => VerbFilter::class,
          'actions' => [
            'delete' => ['POST'],
          ],
        ],
      ]
    );
  }

  public function actionFinancial()
  {
    $dateRange = Yii::$app->request->get('date_range');
    $range = $this->resolveDateRange($dateRange);
    $startDate = $range['start'];
    $endDate = $range['end'];
    $displayRange = $range['display'];

    $start = $startDate;
    $end = $endDate;
    $paidStatuses = [Invoice::STATUS_PAID, Invoice::STATUS_PROCESS];

    $totalRevenue = (float) Invoice::find()
      ->where(['between', 'date', $start, $end])
      ->andWhere(['IN', 'status', $paidStatuses])
      ->sum('paid_amount');

    $invoiceCount = (int) Invoice::find()
      ->where(['between', 'date', $start, $end])
      ->andWhere(['IN', 'status', $paidStatuses])
      ->count();

    $totalExpenseOnly = (float) Expense::find()
      ->where(['between', 'date', $start, $end])
      ->andWhere(['!=', 'status', Expense::STATUS_CANCELLED])
      ->sum('grand_total');

    $expenseCount = (int) Expense::find()
      ->where(['between', 'date', $start, $end])
      ->andWhere(['!=', 'status', Expense::STATUS_CANCELLED])
      ->count();

    $poStatuses = [PurchaseOrder::STATUS_PROCESS, PurchaseOrder::STATUS_PAID];

    $totalPurchaseOrders = (float) PurchaseOrder::find()
      ->where(['between', 'date', $start, $end])
      ->andWhere(['IN', 'status', $poStatuses])
      ->sum('grand_total');

    $poCount = (int) PurchaseOrder::find()
      ->where(['between', 'date', $start, $end])
      ->andWhere(['IN', 'status', $poStatuses])
      ->count();

    $recentInvoices = Invoice::find()
      ->with('customer')
      ->where(['between', 'date', $start, $end])
      ->andWhere(['IN', 'status', $paidStatuses])
      ->orderBy(['date' => SORT_DESC])
      ->limit(5)
      ->all();

    $recentExpenses = Expense::find()
      ->with('supplier')
      ->where(['between', 'date', $start, $end])
      ->andWhere(['!=', 'status', Expense::STATUS_CANCELLED])
      ->orderBy(['date' => SORT_DESC])
      ->limit(5)
      ->all();

    $recentPurchaseOrders = PurchaseOrder::find()
      ->with('supplier')
      ->where(['between', 'date', $start, $end])
      ->andWhere(['IN', 'status', $poStatuses])
      ->orderBy(['date' => SORT_DESC])
      ->limit(5)
      ->all();

    $financialRows = array_merge(
      array_map(
        fn(Expense $expense) => ['type' => 'expense', 'model' => $expense],
        $recentExpenses,
      ),
      array_map(
        fn(PurchaseOrder $po) => ['type' => 'purchase-order', 'model' => $po],
        $recentPurchaseOrders,
      ),
    );
    usort(
      $financialRows,
      fn($a, $b) => strtotime($b['model']->date) <=>
        strtotime($a['model']->date),
    );
    $financialRows = array_slice($financialRows, 0, 5);

    $revenueByDay = Invoice::find()
      ->select([
        'period' => new Expression('date'),
        'total' => new Expression('SUM(paid_amount)'),
      ])
      ->where(['between', 'date', $start, $end])
      ->andWhere(['IN', 'status', $paidStatuses])
      ->groupBy('period')
      ->orderBy('period')
      ->asArray()
      ->all();

    $expenseByDay = Expense::find()
      ->select([
        'period' => new Expression('date'),
        'total' => new Expression('SUM(grand_total)'),
      ])
      ->where(['between', 'date', $start, $end])
      ->andWhere(['!=', 'status', Expense::STATUS_CANCELLED])
      ->groupBy('period')
      ->orderBy('period')
      ->asArray()
      ->all();

    $poByDay = PurchaseOrder::find()
      ->select([
        'period' => new Expression('date'),
        'total' => new Expression('SUM(grand_total)'),
      ])
      ->where(['between', 'date', $start, $end])
      ->andWhere(['IN', 'status', $poStatuses])
      ->groupBy('period')
      ->orderBy('period')
      ->asArray()
      ->all();

    $revenueMap = ArrayHelper::map($revenueByDay, 'period', 'total');
    $expenseMap = ArrayHelper::map($expenseByDay, 'period', 'total');
    $poMap = ArrayHelper::map($poByDay, 'period', 'total');

    $dailyReport = [];
    $periodEnd = (new DateTime($endDate))->modify('+1 day');
    $periodIterator = new DatePeriod(
      new DateTime($startDate),
      new DateInterval('P1D'),
      $periodEnd,
    );
    foreach ($periodIterator as $currentDate) {
      $key = $currentDate->format('Y-m-d');
      $revenue = (float) ($revenueMap[$key] ?? 0);
      $expense = (float) ($expenseMap[$key] ?? 0);
      $expense += (float) ($poMap[$key] ?? 0);
      $dailyReport[] = [
        'label' => $currentDate->format('d M, Y'),
        'revenue' => $revenue,
        'expense' => $expense,
        'net' => $revenue - $expense,
      ];
    }

    $totalExpenses = $totalExpenseOnly + $totalPurchaseOrders;
    $netProfit = $totalRevenue - $totalExpenses;

    return $this->render('financial', [
      'dateRange' => $displayRange,
      'totalRevenue' => $totalRevenue,
      'totalExpenses' => $totalExpenses,
      'netProfit' => $netProfit,
      'invoiceCount' => $invoiceCount,
      'expenseCount' => $expenseCount,
      'poCount' => $poCount,
      'dailyReport' => $dailyReport,
      'recentInvoices' => $recentInvoices,
      'financialRows' => $financialRows,
    ]);
  }

  public function actionCustomerRevenue()
  {
    $dateRange = Yii::$app->request->get('date_range');
    $range = $this->resolveDateRange($dateRange);
    $startDate = $range['start'];
    $endDate = $range['end'];
    $displayRange = $range['display'];

    $start = $startDate;
    $end = $endDate;
    $paidStatuses = [Invoice::STATUS_PAID, Invoice::STATUS_PROCESS];

    $totalRevenue = (float) Invoice::find()
      ->where(['between', 'date', $start, $end])
      ->andWhere(['IN', 'status', $paidStatuses])
      ->sum('paid_amount');

    $invoiceCount = (int) Invoice::find()
      ->where(['between', 'date', $start, $end])
      ->andWhere(['IN', 'status', $paidStatuses])
      ->count();

    $uniqueCustomers = (int) Invoice::find()
      ->where(['between', 'date', $start, $end])
      ->andWhere(['IN', 'status', $paidStatuses])
      ->andWhere(['IS NOT', 'customer_id', null])
      ->select('customer_id')
      ->distinct()
      ->count();

    // Total margins
    $totalMargin =
      (float) (\app\models\Invoice::find()
        ->where(['between', 'invoice.date', $start, $end])
        ->andWhere(['IN', 'invoice.status', $paidStatuses])
        ->sum('grand_total - cost_total') ?? 0);

    $recentInvoices = Invoice::find()
      ->with('customer')
      ->where(['between', 'date', $start, $end])
      ->andWhere(['IN', 'status', $paidStatuses])
      ->orderBy(['date' => SORT_DESC])
      ->limit(5)
      ->all();

    $topCustomers = Invoice::find()
      ->alias('invoice')
      ->select([
        'customer_id',
        'customer_name' => new Expression('customer.name'),
        'total_revenue' => new Expression('SUM(invoice.paid_amount)'),
        'invoice_count' => new Expression('COUNT(invoice.id)'),
      ])
      ->leftJoin('customer', 'customer.id = invoice.customer_id')
      ->where(['between', 'invoice.date', $start, $end])
      ->andWhere(['IN', 'invoice.status', $paidStatuses])
      ->groupBy('invoice.customer_id')
      ->orderBy(['total_revenue' => SORT_DESC])
      ->limit(10)
      ->asArray()
      ->all();

    return $this->render('customer-revenue', [
      'dateRange' => $displayRange,
      'totalRevenue' => $totalRevenue,
      'invoiceCount' => $invoiceCount,
      'uniqueCustomers' => $uniqueCustomers,
      'averageInvoice' => $invoiceCount ? $totalRevenue / $invoiceCount : 0,
      'topCustomers' => $topCustomers,
      'recentInvoices' => $recentInvoices,
      'totalMargin' => $totalMargin,
    ]);
  }

  private function resolveDateRange(?string $input): array
  {
    $start = date('Y-m-01');
    $end = date('Y-m-d');
    if ($input) {
      $parts = explode(' to ', $input);
      if (count($parts) === 2) {
        // Range format: "start date to end date"
        $parsedStart = strtotime($parts[0]);
        $parsedEnd = strtotime($parts[1]);
        if ($parsedStart && $parsedEnd) {
          $start = date('Y-m-d', $parsedStart);
          $end = date('Y-m-d', $parsedEnd);
        }
      } else {
        // Single day format: just one date
        $parsedDate = strtotime($input);
        if ($parsedDate) {
          $start = date('Y-m-d', $parsedDate);
          $end = date('Y-m-d', $parsedDate);
        }
      }
    }

    $display =
      $input ?:
      date('d M, Y', strtotime($start)) .
      ' to ' .
      date('d M, Y', strtotime($end));

    return [
      'start' => $start,
      'end' => $end,
      'display' => $display,
    ];
  }

  public function actionInventory()
  {
    $dateRange = Yii::$app->request->get('date_range');
    $range = $this->resolveDateRange($dateRange);
    $startDate = $range['start'];
    $endDate = $range['end'];
    $displayRange = $range['display'];

    $start = $startDate;
    $end = $endDate;

    $incoming = (int) Inventory::find()
      ->where(['between', 'date', $start, $end])
      ->sum('`in`');

    $outgoing = (int) Inventory::find()
      ->where(['between', 'date', $start, $end])
      ->sum('`out`');

    $netMovement = $incoming - $outgoing;

    $topProducts = Inventory::find()
      ->alias('inventory')
      ->select([
        'inventory.product_id',
        'product_name' => new Expression('product.name'),
        'incoming' => new Expression('SUM(inventory.in)'),
        'outgoing' => new Expression('SUM(inventory.out)'),
        'net' => new Expression('SUM(inventory.in - inventory.out)'),
      ])
      ->leftJoin('product', 'product.id = inventory.product_id')
      ->where(['between', 'inventory.date', $start, $end])
      ->groupBy('inventory.product_id')
      ->orderBy(['net' => SORT_DESC])
      ->limit(10)
      ->asArray()
      ->all();

    $dailyMovements = Inventory::find()
      ->select([
        'period' => new Expression('date'),
        'incoming' => new Expression('SUM(inventory.in)'),
        'outgoing' => new Expression('SUM(inventory.out)'),
        'net' => new Expression('SUM(inventory.in - inventory.out)'),
      ])
      ->where(['between', 'date', $start, $end])
      ->groupBy('period')
      ->orderBy('period')
      ->asArray()
      ->all();

    $recentLogs = Inventory::find()
      ->with('product')
      ->where(['between', 'date', $start, $end])
      ->orderBy(['date' => SORT_DESC])
      ->limit(5)
      ->all();

    return $this->render('inventory', [
      'dateRange' => $displayRange,
      'incoming' => $incoming,
      'outgoing' => $outgoing,
      'netMovement' => $netMovement,
      'topProducts' => $topProducts,
      'dailyMovements' => $dailyMovements,
      'recentLogs' => $recentLogs,
    ]);
  }

  public function actionSales()
  {
    $dateRange = Yii::$app->request->get('date_range');
    $range = $this->resolveDateRange($dateRange);
    $startDate = $range['start'];
    $endDate = $range['end'];
    $displayRange = $range['display'];

    $start = $startDate;
    $end = $endDate;
    $paidStatuses = [Invoice::STATUS_PAID, Invoice::STATUS_PROCESS];

    $totalSales = (float) Invoice::find()
      ->where(['between', 'invoice.date', $start, $end])
      ->andWhere(['IN', 'status', $paidStatuses])
      ->sum('grand_total');

    $invoiceCount = (int) Invoice::find()
      ->where(['between', 'date', $start, $end])
      ->andWhere(['IN', 'status', $paidStatuses])
      ->count();

    $totalUnits = (int) InvoiceItem::find()
      ->alias('invoice_item')
      ->leftJoin('invoice', 'invoice.id = invoice_item.invoice_id')
      ->where(['between', 'invoice.date', $start, $end])
      ->andWhere(['IN', 'invoice.status', $paidStatuses])
      ->sum('invoice_item.quantity');

    // Total margins
    $totalMargin =
      (float) (\app\models\Invoice::find()
        ->where(['between', 'invoice.date', $start, $end])
        ->andWhere(['IN', 'invoice.status', $paidStatuses])
        ->sum('grand_total - cost_total') ?? 0);

    $dailySales = Invoice::find()
      ->select([
        'period' => new Expression('date'),
        'orders' => new Expression('COUNT(id)'),
        'total' => new Expression('SUM(grand_total)'),
        'margins' => new Expression('SUM(grand_total - cost_total)'),
      ])
      ->where(['between', 'date', $start, $end])
      ->andWhere(['IN', 'status', $paidStatuses])
      ->groupBy('period')
      ->orderBy(['period' => SORT_DESC])
      ->asArray()
      ->all();

    $topProducts = InvoiceItem::find()
      ->alias('invoice_item')
      ->select([
        'invoice_item.product_name',
        'invoice_item.product_id',
        'quantity' => new Expression('SUM(invoice_item.quantity)'),
        'revenue' => new Expression(
          'SUM(invoice_item.price * invoice_item.quantity)',
        ),
      ])
      ->leftJoin('invoice', 'invoice.id = invoice_item.invoice_id')
      ->where(['between', 'invoice.date', $start, $end])
      ->andWhere(['IN', 'invoice.status', $paidStatuses])
      ->groupBy('invoice_item.product_id', 'invoice_item.product_name')
      ->orderBy(['quantity' => SORT_DESC])
      ->limit(10)
      ->asArray()
      ->all();

    $recentInvoices = Invoice::find()
      ->with('customer')
      ->where(['between', 'date', $start, $end])
      ->andWhere(['IN', 'status', $paidStatuses])
      ->orderBy(['date' => SORT_DESC])
      ->limit(5)
      ->all();

    return $this->render('sales', [
      'dateRange' => $displayRange,
      'totalSales' => $totalSales,
      'invoiceCount' => $invoiceCount,
      'totalMargin' => $totalMargin,
      'totalUnits' => $totalUnits,
      'dailySales' => $dailySales,
      'topProducts' => $topProducts,
      'recentInvoices' => $recentInvoices,
    ]);
  }
}
