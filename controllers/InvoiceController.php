<?php

namespace app\controllers;

use app\models\ActivityLog;
use app\models\Customer;
use app\models\Inventory;
use app\models\Product;
use app\models\InvoiceItem;
use app\models\Invoice;
use app\models\InvoicePayment;
use app\models\InvoiceSearch;
use app\models\PaymentMethod;
use Exception;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * InvoiceController provides index and view pages for invoices.
 */
class InvoiceController extends Controller
{
  /**
   * @inheritDoc
   */
  public function behaviors()
  {
    return array_merge(parent::behaviors(), [
      'verbs' => [
        'class' => VerbFilter::class,
        'actions' => [
          'add-payment' => ['post'],
        ],
      ],
    ]);
  }

  /**
   * Lists all Invoice models.
   *
   * @return string
   */
  public function actionIndex()
  {
    $searchModel = new InvoiceSearch();
    $dataProvider = $searchModel->search($this->request->queryParams);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
      'customers' => $this->getCustomers(),
    ]);
  }
  /**
   * Displays a single Invoice model.
   * @param int $id ID
   * @return string
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionView($id)
  {
    $model = $this->findModel($id);
    return $this->render('view', $this->prepareInvoiceViewData($model));
  }

  public function actionAddPayment($id)
  {
    $invoice = $this->findModel($id);

    if ($invoice->status == Invoice::STATUS_CANCELLED) {
      Yii::$app->session->setFlash(
        'error',
        'Cannot record payment for a cancelled invoice.',
      );
      return $this->redirect(['view', 'id' => $invoice->id]);
    }

    $paymentModel = new InvoicePayment(['date' => date('Y-m-d')]);
    $paymentModel->invoice_id = $invoice->id;

    if ($this->request->isPost && $paymentModel->load($this->request->post())) {
      $paymentModel->code = $this->generatePaymentCode($invoice);
      $paymentModel->created_at = date('Y-m-d H:i:s');
      $paymentModel->created_by = Yii::$app->user->id;
      $paymentModel->amount = round((float) $paymentModel->amount, 2);
      $balance = round((float) $invoice->balance_amount, 2);

      if ($balance <= 0) {
        $paymentModel->addError('amount', 'Invoice is already fully paid.');
      }
      if ($paymentModel->amount <= 0) {
        $paymentModel->addError(
          'amount',
          'Payment amount must be greater than zero.',
        );
      }
      if ($paymentModel->amount > $balance) {
        $paymentModel->addError(
          'amount',
          'Amount cannot exceed the remaining balance.',
        );
      }

      if (!$paymentModel->hasErrors()) {
        $transaction = Yii::$app->db->beginTransaction();
        try {
          if (!$paymentModel->save()) {
            throw new Exception('Failed to record payment.');
          }

          $invoice->paid_amount = round(
            $invoice->paid_amount + $paymentModel->amount,
            2,
          );
          $invoice->balance_amount = max(
            0,
            round($invoice->grand_total - $invoice->paid_amount, 2),
          );
          if ($invoice->balance_amount <= 0) {
            $invoice->status = Invoice::STATUS_PAID;
            $invoice->balance_amount = 0;
          } elseif ($invoice->status !== Invoice::STATUS_PROCESS) {
            $invoice->status = Invoice::STATUS_PROCESS;
          }

          if (
            !$invoice->save(false, ['paid_amount', 'balance_amount', 'status'])
          ) {
            throw new Exception('Failed to sync invoice totals.');
          }

          $transaction->commit();
          try {
            Yii::$app->utils::insertActivityLog([
              'action' => 'payment',
              'params' => [
                'invoice_id' => $invoice->id,
                'payment_id' => $paymentModel->id,
                'amount' => $paymentModel->amount,
              ],
            ]);
          } catch (\Throwable $e) {
            // logging failure should not block the request
          }

          Yii::$app->session->setFlash(
            'success',
            'Payment recorded successfully.',
          );
          return $this->redirect(['view', 'id' => $invoice->id]);
        } catch (Exception $e) {
          $transaction->rollBack();
          Yii::$app->session->setFlash('error', $e->getMessage());
        }
      } else {
        Yii::$app->session->setFlash(
          'error',
          'Please fix the payment input errors before submitting.',
        );
      }
    }

    return $this->render(
      'view',
      $this->prepareInvoiceViewData($invoice, $paymentModel),
    );
  }

  /**
   * Creates a new Invoice model.
   * If creation is successful, the browser will be redirected to the 'index' page.
   * @return string|\yii\web\Response
   */
  public function actionCreate()
  {
    $model = new Invoice();
    $model->date = date('Y-m-d');
    $model->due_date = date('Y-m-d', strtotime('+7 days'));
    $model->code = $this->generateCode();
    $model->serial_code = $this->generateSerialCode();

    if ($this->request->isPost) {
      if ($model->load($this->request->post())) {
        $transaction = Yii::$app->db->beginTransaction();
        try {
          $model->paid_amount = 0;
          $model->balance_amount = $model->grand_total;
          $model->status = Invoice::STATUS_ACTIVE;
          if (!$model->save()) {
            throw new Exception('Failed to save Invoice header.');
          }

          $items = $this->request->post('InvoiceItem', []);
          foreach ($items as $itemData) {
            $item = new InvoiceItem();
            $item->invoice_id = $model->id;
            if ($item->load($itemData, '')) {
              if (!$item->save()) {
                $errors = implode(
                  '<br>',
                  \yii\helpers\ArrayHelper::getColumn($item->getErrors(), 0),
                );
                throw new Exception('Failed to save Invoice item: ' . $errors);
              }
              // track inventory - invoices reduce stock (OUT)
              Yii::$app->utils::updateInventory(
                $item->product_id,
                $item->quantity,
                $model->id,
                Inventory::TYPE_INVOICE,
                'out',
              );
            }
          }

          $transaction->commit();
          try {
            Yii::$app->utils::insertActivityLog([
              'params' => array_merge(Yii::$app->request->post(), [
                'id' => $model->id,
              ]),
            ]);
          } catch (\Throwable $e) {
            // do not block request on logging failure
          }
          Yii::$app->session->setFlash(
            'success',
            'Invoice created successfully.',
          );
          return $this->redirect(['view', 'id' => $model->id]);
        } catch (Exception $e) {
          $transaction->rollBack();
          Yii::$app->session->setFlash('error', $e->getMessage());
          print_r($e->getMessage());
          exit();
        }
      }
    }

    return $this->render('create', [
      'model' => $model,
      'customers' => $this->getCustomers(),
      'products' => $this->getProducts(),
    ]);
  }

  /**
   * Updates an existing Invoice model.
   * If update is successful, the browser will be redirected to the 'index' page.
   * @param int $id ID
   * @return string|\yii\web\Response
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionUpdate($id)
  {
    $model = $this->findModel($id);

    if (
      $model->status == Invoice::STATUS_CANCELLED ||
      $model->status == Invoice::STATUS_PAID
    ) {
      Yii::$app->session->setFlash(
        'error',
        'This Invoice cannot be updated after it is cancelled or paid.',
      );
      return $this->redirect(['view', 'id' => $model->id]);
    }

    if ($this->request->isPost && $model->load($this->request->post())) {
      $transaction = Yii::$app->db->beginTransaction();
      try {
        $model->paid_amount = 0;
        $model->balance_amount = $model->grand_total;
        if (!$model->save()) {
          throw new Exception('Failed to update Invoice header.');
        }

        // Roll inventory back to the pre-invoice state by deleting the old records
        $oldItems = InvoiceItem::findAll(['invoice_id' => $model->id]);
        foreach ($oldItems as $oldItem) {
          Product::updateAllCounters(
            ['available' => $oldItem->quantity],
            ['id' => $oldItem->product_id],
          );
        }
        Inventory::deleteAll([
          'type' => Inventory::TYPE_INVOICE,
          'transaction_id' => $model->id,
        ]);

        InvoiceItem::deleteAll(['invoice_id' => $model->id]);
        $items = $this->request->post('InvoiceItem', []);
        foreach ($items as $itemData) {
          $item = new InvoiceItem();
          $item->invoice_id = $model->id;
          if ($item->load($itemData, '')) {
            if (!$item->save()) {
              $errors = implode(
                '<br>',
                \yii\helpers\ArrayHelper::getColumn($item->getErrors(), 0),
              );
              throw new Exception('Failed to save Invoice item: ' . $errors);
            }
            // track inventory - invoices reduce stock (OUT)
            Yii::$app->utils::updateInventory(
              $item->product_id,
              $item->quantity,
              $model->id,
              Inventory::TYPE_INVOICE,
              'out',
            );
          }
        }

        $transaction->commit();
        try {
          Yii::$app->utils::insertActivityLog([
            'params' => array_merge(Yii::$app->request->post(), [
              'id' => $model->id,
            ]),
          ]);
        } catch (\Throwable $e) {
          // do not block request on logging failure
        }
        Yii::$app->session->setFlash(
          'success',
          'Invoice updated successfully.',
        );
        return $this->redirect(['view', 'id' => $model->id]);
      } catch (Exception $e) {
        $transaction->rollBack();
        Yii::$app->session->setFlash('error', $e->getMessage());
      }
    }

    return $this->render('update', [
      'model' => $model,
      'customers' => $this->getCustomers(),
      'products' => $this->getProducts(),
    ]);
  }

  /**
   * Deletes an existing Invoice model.
   * If deletion is successful, the browser will be redirected to the 'index' page.
   * @param int $id ID
   * @return \yii\web\Response
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionDelete($id)
  {
    $model = $this->findModel($id);
    $transaction = Yii::$app->db->beginTransaction();
    try {
      // Reverse inventory for all items
      $items = InvoiceItem::findAll(['invoice_id' => $model->id]);
      foreach ($items as $item) {
        Yii::$app->utils::updateInventory(
          $item->product_id,
          $item->quantity,
          $model->id,
          Inventory::TYPE_INVOICE,
          'in',
        );
      }

      InvoiceItem::deleteAll(['invoice_id' => $model->id]);
      $model->delete();
      $transaction->commit();
      try {
        Yii::$app->utils::insertActivityLog([
          'params' => ['id' => $model->id],
        ]);
      } catch (\Throwable $e) {
        // do not block request on logging failure
      }
      Yii::$app->session->setFlash('success', 'Invoice deleted successfully.');
    } catch (Exception $e) {
      $transaction->rollBack();
      Yii::$app->session->setFlash('error', 'Failed to delete Invoice.');
    }

    return $this->redirect(['index']);
  }

  /**
   * Duplicates a Invoice into a new, unsaved record.
   * @param int $id ID
   * @return string
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionDuplicate($id)
  {
    $model = $this->findModel($id);

    $duplicate = new Invoice();
    $duplicate->customer_id = $model->customer_id;
    $duplicate->date = date('Y-m-d');
    $duplicate->due_date = date('Y-m-d', strtotime('+7 days'));
    $duplicate->code = $this->generateCode();
    $duplicate->serial_code = $this->generateSerialCode();
    $duplicate->remark = $model->remark;
    $duplicate->delivery_fee = $model->delivery_fee;
    $duplicate->extra_charge = $model->extra_charge;
    $duplicate->discount_amount = $model->discount_amount;
    $duplicate->cost_total = $model->cost_total;
    $duplicate->sub_total = $model->sub_total;
    $duplicate->grand_total = $model->grand_total;
    $duplicate->paid_amount = 0;
    $duplicate->balance_amount = $model->grand_total;
    $duplicate->status = Invoice::STATUS_ACTIVE;

    $items = [];
    foreach ($model->items as $item) {
      $duplicateItem = new InvoiceItem();
      $duplicateItem->attributes = $item->attributes;
      $duplicateItem->id = null;
      $duplicateItem->invoice_id = null;
      $items[] = $duplicateItem;
    }

    return $this->render('create', [
      'model' => $duplicate,
      'customers' => $this->getCustomers(),
      'products' => $this->getProducts(),
      'items' => $items,
      'isDuplicate' => true,
    ]);
  }

  /**
   * Cancels an existing Invoice model.
   * @param int $id ID
   * @return \yii\web\Response
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionCancel($id)
  {
    $model = $this->findModel($id);

    if ($model->status == Invoice::STATUS_CANCELLED) {
      Yii::$app->session->setFlash('info', 'Invoice is already cancelled.');
      return $this->redirect(['view', 'id' => $model->id]);
    }

    // Reverse inventory for all items
    $items = InvoiceItem::findAll(['invoice_id' => $model->id]);
    foreach ($items as $item) {
      Yii::$app->utils::updateInventory(
        $item->product_id,
        $item->quantity,
        $model->id,
        Inventory::TYPE_INVOICE,
        'in',
      );
    }

    $model->status = Invoice::STATUS_CANCELLED;
    if ($model->save(false)) {
      try {
        Yii::$app->utils::insertActivityLog([
          'params' => ['id' => $model->id],
        ]);
      } catch (\Throwable $e) {
        // do not block request on logging failure
      }
      Yii::$app->session->setFlash(
        'success',
        'Invoice cancelled successfully.',
      );
      return $this->redirect(['view', 'id' => $model->id]);
    }

    Yii::$app->session->setFlash('error', 'Failed to cancel Invoice.');
    return $this->redirect(['view', 'id' => $model->id]);
  }

  /**
   * Gather shared payload for the invoice view.
   *
   * @param Invoice $invoice
   * @param InvoicePayment|null $paymentModel
   * @return array
   */
  protected function prepareInvoiceViewData(
    Invoice $invoice,
    InvoicePayment $paymentModel = null,
  ) {
    $outlet = \app\models\Outlet::find()->one();
    $activities = $this->loadInvoiceActivities($invoice->id);

    if ($paymentModel === null) {
      $paymentModel = new InvoicePayment(['date' => date('Y-m-d')]);
    }
    $paymentModel->invoice_id = $invoice->id;
    if ($paymentModel->amount === null) {
      $paymentModel->amount = (float) $invoice->balance_amount;
    }
    if (!$paymentModel->date) {
      $paymentModel->date = date('Y-m-d');
    }

    $payments = InvoicePayment::find()
      ->where(['invoice_id' => $invoice->id])
      ->orderBy(['date' => SORT_DESC, 'id' => SORT_DESC])
      ->all();

    return [
      'model' => $invoice,
      'outlet' => $outlet,
      'activities' => $activities,
      'payments' => $payments,
      'paymentModel' => $paymentModel,
      'paymentMethods' => $this->getPaymentMethodList(),
    ];
  }

  protected function loadInvoiceActivities($invoiceId)
  {
    $query = ActivityLog::find()
      ->where([
        'or',
        [
          'and',
          ['controller' => 'invoice'],
          [
            'in',
            'action',
            ['create', 'update', 'delete', 'cancel', 'payment', 'duplicate'],
          ],
        ],
        [
          'and',
          ['controller' => 'quotation'],
          ['in', 'action', ['convert-to-invoice']],
        ],
      ])
      ->andWhere([
        'or',
        ['like', 'params', '"id":' . $invoiceId],
        ['like', 'params', '"id":"' . $invoiceId . '"'],
        ['like', 'params', '"invoice_id":' . $invoiceId],
        ['like', 'params', '"invoice_id":"' . $invoiceId . '"'],
      ]);

    return $query
      ->orderBy(['created_at' => SORT_DESC])
      ->limit(50)
      ->all();
  }

  protected function getPaymentMethodList()
  {
    return ArrayHelper::map(
      PaymentMethod::find()
        ->orderBy(['name' => SORT_ASC])
        ->all(),
      'id',
      'name',
    );
  }

  protected function generatePaymentCode(Invoice $invoice)
  {
    $count = (int) InvoicePayment::find()
      ->where(['invoice_id' => $invoice->id])
      ->count();
    return $invoice->code . '-PAY-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
  }

  /**
   * Finds the Invoice model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param int $id ID
   * @return Invoice the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = Invoice::findOne(['id' => $id])) !== null) {
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }

  protected function getCustomers()
  {
    return ArrayHelper::map(Customer::find()->all(), 'id', 'name');
  }

  protected function getProducts()
  {
    return ArrayHelper::map(Product::find()->all(), 'id', 'name');
  }

  protected function generateCode()
  {
    $last = Invoice::find()
      ->orderBy(['id' => SORT_DESC])
      ->one();
    $num = $last ? (int) substr($last->code, 4) + 1 : 1;
    return 'INV-' . str_pad($num, 5, '0', STR_PAD_LEFT);
  }

  protected function generateSerialCode()
  {
    return date('Ymd') .
      '-' .
      strtoupper(Yii::$app->security->generateRandomString(4));
  }

  protected function generateInvoiceCode()
  {
    $last = Invoice::find()
      ->orderBy(['id' => SORT_DESC])
      ->one();
    $num = 1;
    if ($last && preg_match('/^INV-(\d+)$/', $last->code, $matches)) {
      $num = (int) $matches[1] + 1;
    }
    return 'INV-' . str_pad($num, 5, '0', STR_PAD_LEFT);
  }

  protected function generateInvoiceSerialCode()
  {
    return date('Ymd') .
      '-' .
      strtoupper(Yii::$app->security->generateRandomString(4));
  }

  public function actionGetProduct($id)
  {
    $product = Product::findOne($id);
    if ($product) {
      return $this->asJson([
        'success' => true,
        'data' => [
          'id' => $product->id,
          'name' => $product->name,
          'sku' => $product->sku,
          'serial' => $product->serial,
          'price' => $product->price,
          'cost' => $product->cost,
          'description' => $product->description,
        ],
      ]);
    }
    return $this->asJson(['success' => false]);
  }

  public function actionProductSearch($q = null)
  {
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    $out = ['results' => ['id' => '', 'text' => '']];
    if (!is_null($q)) {
      $query = Product::find()
        ->select([
          'id',
          'name',
          'sku',
          'serial',
          'price',
          'cost',
          'description',
        ])
        ->where(['like', 'name', $q])
        ->orWhere(['like', 'sku', $q])
        ->orWhere(['like', 'serial', $q])
        ->limit(20)
        ->asArray()
        ->all();

      $results = [];
      foreach ($query as $row) {
        $results[] = [
          'id' => $row['id'],
          'text' => $row['name'] . ' (' . $row['sku'] . ')',
          'data' => $row,
        ];
      }
      $out['results'] = $results;
    }
    return $out;
  }
}
