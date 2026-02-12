<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
  /**
   * {@inheritdoc}
   */
  public function behaviors()
  {
    return [
      'access' => [
        'class' => AccessControl::class,
        'only' => ['logout'],
        'rules' => [
          [
            'actions' => ['logout'],
            'allow' => true,
            'roles' => ['@'],
          ],
        ],
      ],
      'verbs' => [
        'class' => VerbFilter::class,
        'actions' => [
          'logout' => ['post'],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function actions()
  {
    return [
      'error' => [
        'class' => 'yii\web\ErrorAction',
      ],
      'captcha' => [
        'class' => 'yii\captcha\CaptchaAction',
        'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
      ],
    ];
  }

  /**
   * Displays homepage.
   *
   * @return string
   */
  public function actionIndex()
  {
    $dateRange = Yii::$app->request->get('date_range');
    $startDate = date('Y-m-01');
    $endDate = date('Y-m-d');

    if ($dateRange) {
      $dates = explode(' to ', $dateRange);
      if (count($dates) == 2) {
        $startTime = strtotime($dates[0]);
        $endTime = strtotime($dates[1]);
        if ($startTime && $endTime) {
          $startDate = date('Y-m-d', $startTime);
          $endDate = date('Y-m-d', $endTime);
        }
      }
    }

    $start = $startDate . ' 00:00:00';
    $end = $endDate . ' 23:59:59';

    // Fetch dynamic statistics from database
    // Total invoices (all statuses)
    $totalInvoices = (int) \app\models\Invoice::find()
      ->where(['between', 'created_at', $start, $end])
      ->count();

    // Total revenue (paid invoices only)
    $totalRevenue =
      (float) (\app\models\Invoice::find()
        ->where(['between', 'created_at', $start, $end])
        ->andWhere([
          'IN',
          'status',
          [
            \app\models\Invoice::STATUS_PAID,
            \app\models\Invoice::STATUS_PROCESS,
          ],
        ])
        ->sum('paid_amount') ?? 0);

    // Total POs
    $totalPOs = (int) \app\models\PurchaseOrder::find()
      ->where(['between', 'created_at', $start, $end])
      ->count();

    // Total PO amount
    $totalPOAmount =
      (float) (\app\models\PurchaseOrder::find()
        ->where(['between', 'created_at', $start, $end])
        ->sum('grand_total') ?? 0);

    // Total customers
    $totalCustomers = (int) \app\models\Customer::find()
      ->where(['between', 'created_at', $start, $end])
      ->count();

    // Total products
    $totalProducts = (int) \app\models\Product::find()
      ->where(['between', 'created_at', $start, $end])
      ->count();

    // Total stock
    $totalStock = (int) (\app\models\Product::find()->sum('available') ?? 0);

    // Recent invoices (last 5)
    $recentInvoices = \app\models\Invoice::find()
      ->with('customer')
      ->where(['between', 'created_at', $start, $end])
      ->orderBy(['created_at' => SORT_DESC])
      ->limit(5)
      ->all();

    // Recent purchase orders (last 5)
    $recentPOs = \app\models\PurchaseOrder::find()
      ->with('supplier')
      ->where(['between', 'created_at', $start, $end])
      ->orderBy(['created_at' => SORT_DESC])
      ->limit(5)
      ->all();

    // Top products by stock
    $topProducts = \app\models\Product::find()
      ->with('category', 'brand')
      ->orderBy(['available' => SORT_DESC])
      ->limit(5)
      ->all();

    // Top products by sales (Best Sellers)
    $bestSellers = \app\models\InvoiceItem::find()
      ->select([
        'invoice_item.product_id',
        'invoice_item.product_name',
        'qty' => 'SUM(invoice_item.quantity)',
      ])
      ->leftJoin('invoice', 'invoice.id = invoice_item.invoice_id')
      ->where(['between', 'invoice.created_at', $start, $end])
      ->andWhere([
        'IN',
        'invoice.status',
        [\app\models\Invoice::STATUS_PAID, \app\models\Invoice::STATUS_PROCESS],
      ])
      ->groupBy(['invoice_item.product_id', 'invoice_item.product_name'])
      ->orderBy(['qty' => SORT_DESC])
      ->limit(5)
      ->asArray()
      ->all();

    // Recent activity (last 20)
    $recentActivities = \app\models\ActivityLog::find()
      ->with('user')
      ->orderBy(['created_at' => SORT_DESC])
      ->limit(20)
      ->all();

    return $this->render('index', [
      'totalInvoices' => $totalInvoices,
      'totalRevenue' => $totalRevenue,
      'totalPOs' => $totalPOs,
      'totalPOAmount' => $totalPOAmount,
      'totalCustomers' => $totalCustomers,
      'totalProducts' => $totalProducts,
      'totalStock' => $totalStock,
      'recentInvoices' => $recentInvoices,
      'recentPOs' => $recentPOs,
      'topProducts' => $topProducts,
      'bestSellers' => $bestSellers,
      'recentActivities' => $recentActivities,
    ]);
  }

  /**
   * Login action.
   *
   * @return Response|string
   */
  public function actionLogin()
  {
    if (!Yii::$app->user->isGuest) {
      return $this->goHome();
    }

    $model = new LoginForm();
    if ($model->load(Yii::$app->request->post()) && $model->login()) {
      return $this->goBack();
    }

    $model->password = '';
    return $this->render('login', [
      'model' => $model,
    ]);
  }

  /**
   * Logout action.
   *
   * @return Response
   */
  public function actionLogout()
  {
    Yii::$app->user->logout();

    return $this->goHome();
  }

  /**
   * Displays contact page.
   *
   * @return Response|string
   */
  public function actionContact()
  {
    $model = new ContactForm();
    if (
      $model->load(Yii::$app->request->post()) &&
      $model->contact(Yii::$app->params['adminEmail'])
    ) {
      Yii::$app->session->setFlash('contactFormSubmitted');

      return $this->refresh();
    }
    return $this->render('contact', [
      'model' => $model,
    ]);
  }

  /**
   * Displays about page.
   *
   * @return string
   */
  public function actionAbout()
  {
    return $this->render('about');
  }
}
