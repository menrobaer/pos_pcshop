<?php

namespace app\controllers;

use app\models\ActivityLog;
use app\models\Inventory;
use app\models\PaymentMethod;
use app\models\Product;
use app\models\PurchaseOrderItem;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderPayment;
use app\models\PurchaseOrderSearch;
use app\models\Supplier;
use Exception;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * PurchaseOrderController provides index and view pages for purchase orders.
 */
class PurchaseOrderController extends Controller
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
   * Lists all PurchaseOrder models.
   *
   * @return string
   */
  public function actionIndex()
  {
    $searchModel = new PurchaseOrderSearch();
    $dataProvider = $searchModel->search($this->request->queryParams);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
      'suppliers' => $this->getSuppliers(),
    ]);
  }
  /**
   * Displays a single PurchaseOrder model.
   * @param int $id ID
   * @return string
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionView($id)
  {
    $model = $this->findModel($id);
    return $this->render('view', $this->preparePurchaseOrderViewData($model));
  }

  public function actionAddPayment($id)
  {
    $model = $this->findModel($id);
    if ($model->status == PurchaseOrder::STATUS_CANCELLED) {
      Yii::$app->session->setFlash(
        'error',
        'Cannot record payment for a cancelled purchase order.',
      );
      return $this->redirect(['view', 'id' => $model->id]);
    }

    $paymentModel = new PurchaseOrderPayment(['date' => date('Y-m-d')]);
    $paymentModel->purchase_order_id = $model->id;

    if ($this->request->isPost && $paymentModel->load($this->request->post())) {
      $paymentModel->code = $this->generatePurchaseOrderPaymentCode($model);
      $paymentModel->created_at = date('Y-m-d H:i:s');
      $paymentModel->created_by = Yii::$app->user->id;
      $paymentModel->amount = (float) $paymentModel->amount;
      $balance = (float) $model->balance_amount;

      if ($balance <= 0) {
        $paymentModel->addError(
          'amount',
          'Purchase order is already fully paid.',
        );
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

          $model->paid_amount += $paymentModel->amount;
          $model->balance_amount = max(
            0,
            $model->grand_total - $model->paid_amount,
          );
          if ($model->balance_amount <= 0) {
            $model->status = PurchaseOrder::STATUS_PAID;
            $model->balance_amount = 0;
          } elseif ($model->status !== PurchaseOrder::STATUS_PROCESS) {
            $model->status = PurchaseOrder::STATUS_PROCESS;
          }

          if (
            !$model->save(false, ['paid_amount', 'balance_amount', 'status'])
          ) {
            throw new Exception('Failed to sync purchase order totals.');
          }

          $transaction->commit();
          try {
            Yii::$app->utils::insertActivityLog([
              'action' => 'payment',
              'params' => [
                'purchase_order_id' => $model->id,
                'payment_id' => $paymentModel->id,
                'amount' => $paymentModel->amount,
              ],
            ]);
          } catch (\Throwable $e) {
            // Do not block request on logging failure
          }

          Yii::$app->session->setFlash(
            'success',
            'Payment recorded successfully.',
          );
          return $this->redirect(['view', 'id' => $model->id]);
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
      $this->preparePurchaseOrderViewData($model, $paymentModel),
    );
  }

  /**
   * Creates a new PurchaseOrder model.
   * If creation is successful, the browser will be redirected to the 'index' page.
   * @return string|\yii\web\Response
   */
  public function actionCreate()
  {
    $model = new PurchaseOrder();
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
          $model->status = PurchaseOrder::STATUS_ACTIVE;
          if (!$model->save()) {
            throw new Exception('Failed to save PurchaseOrder header.');
          }

          $items = $this->request->post('PurchaseOrderItem', []);
          foreach ($items as $itemData) {
            $item = new PurchaseOrderItem();
            $item->purchase_order_id = $model->id;
            if ($item->load($itemData, '')) {
              if (!$item->save()) {
                $errors = implode(
                  '<br>',
                  \yii\helpers\ArrayHelper::getColumn($item->getErrors(), 0),
                );
                throw new Exception(
                  'Failed to save PurchaseOrder item: ' . $errors,
                );
              }
              // update product latest cost and price
              try {
                $product = Product::findOne($item->product_id);
                if ($product) {
                  $product->cost = $item->cost;
                  $product->price = $item->price;
                  $product->save(false);
                }
              } catch (\Throwable $e) {
                // do not block PO creation on product update failure
              }

              // track inventory
              Yii::$app->utils::updateInventory(
                $item->product_id,
                $item->quantity,
                $model->id,
                Inventory::TYPE_PURCHASE_ORDER,
                'in',
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
            'PurchaseOrder created successfully.',
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
      'suppliers' => $this->getSuppliers(),
      'products' => $this->getProducts(),
    ]);
  }

  /**
   * Updates an existing PurchaseOrder model.
   * If update is successful, the browser will be redirected to the 'index' page.
   * @param int $id ID
   * @return string|\yii\web\Response
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionUpdate($id)
  {
    $model = $this->findModel($id);

    if (
      $model->status == PurchaseOrder::STATUS_CANCELLED ||
      $model->status == PurchaseOrder::STATUS_PAID
    ) {
      Yii::$app->session->setFlash(
        'error',
        'This PurchaseOrder cannot be updated after it is cancelled or paid.',
      );
      return $this->redirect(['view', 'id' => $model->id]);
    }

    if ($this->request->isPost && $model->load($this->request->post())) {
      $transaction = Yii::$app->db->beginTransaction();
      try {
        $model->paid_amount = 0;
        $model->balance_amount = $model->grand_total;
        if (!$model->save()) {
          throw new Exception('Failed to update PurchaseOrder header.');
        }

        // Roll inventory back to the pre-invoice state by deleting the old records
        $oldItems = PurchaseOrderItem::findAll([
          'purchase_order_id' => $model->id,
        ]);
        foreach ($oldItems as $oldItem) {
          Product::updateAllCounters(
            ['available' => -$oldItem->quantity],
            ['id' => $oldItem->product_id],
          );
        }
        Inventory::deleteAll([
          'type' => Inventory::TYPE_PURCHASE_ORDER,
          'transaction_id' => $model->id,
        ]);

        PurchaseOrderItem::deleteAll(['purchase_order_id' => $model->id]);
        $items = $this->request->post('PurchaseOrderItem', []);
        foreach ($items as $itemData) {
          $item = new PurchaseOrderItem();
          $item->purchase_order_id = $model->id;
          if ($item->load($itemData, '')) {
            if (!$item->save()) {
              $errors = implode(
                '<br>',
                \yii\helpers\ArrayHelper::getColumn($item->getErrors(), 0),
              );
              throw new Exception(
                'Failed to save PurchaseOrder item: ' . $errors,
              );
            }
            // update product latest cost and price
            try {
              $product = Product::findOne($item->product_id);
              if ($product) {
                $product->cost = $item->cost;
                $product->price = $item->price;
                $product->save(false);
              }
            } catch (\Throwable $e) {
              // do not block PO update on product update failure
            }

            // track inventory for new items
            Yii::$app->utils::updateInventory(
              $item->product_id,
              $item->quantity,
              $model->id,
              Inventory::TYPE_PURCHASE_ORDER,
              'in',
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
          'PurchaseOrder updated successfully.',
        );
        return $this->redirect(['view', 'id' => $model->id]);
      } catch (Exception $e) {
        $transaction->rollBack();
        Yii::$app->session->setFlash('error', $e->getMessage());
      }
    }

    return $this->render('update', [
      'model' => $model,
      'suppliers' => $this->getSuppliers(),
      'products' => $this->getProducts(),
    ]);
  }

  /**
   * Deletes an existing PurchaseOrder model.
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
      $items = PurchaseOrderItem::findAll(['purchase_order_id' => $model->id]);
      foreach ($items as $item) {
        Yii::$app->utils::updateInventory(
          $item->product_id,
          $item->quantity,
          $model->id,
          Inventory::TYPE_PURCHASE_ORDER,
          'out',
        );
      }

      PurchaseOrderItem::deleteAll(['purchase_order_id' => $model->id]);
      $model->delete();
      $transaction->commit();
      try {
        Yii::$app->utils::insertActivityLog([
          'params' => ['id' => $model->id],
        ]);
      } catch (\Throwable $e) {
        // do not block request on logging failure
      }
      Yii::$app->session->setFlash(
        'success',
        'PurchaseOrder deleted successfully.',
      );
    } catch (Exception $e) {
      $transaction->rollBack();
      Yii::$app->session->setFlash('error', 'Failed to delete PurchaseOrder.');
    }

    return $this->redirect(['index']);
  }

  /**
   * Duplicates a PurchaseOrder into a new, unsaved record.
   * @param int $id ID
   * @return string
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionDuplicate($id)
  {
    $model = $this->findModel($id);

    $duplicate = new PurchaseOrder();
    $duplicate->supplier_id = $model->supplier_id;
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
    $duplicate->status = PurchaseOrder::STATUS_ACTIVE;

    $items = [];
    foreach ($model->items as $item) {
      $duplicateItem = new PurchaseOrderItem();
      $duplicateItem->attributes = $item->attributes;
      $duplicateItem->id = null;
      $duplicateItem->purchase_order_id = null;
      $items[] = $duplicateItem;
    }

    return $this->render('create', [
      'model' => $duplicate,
      'suppliers' => $this->getSuppliers(),
      'products' => $this->getProducts(),
      'items' => $items,
      'isDuplicate' => true,
    ]);
  }

  /**
   * Cancels an existing PurchaseOrder model.
   * @param int $id ID
   * @return \yii\web\Response
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionCancel($id)
  {
    $model = $this->findModel($id);

    if ($model->status == PurchaseOrder::STATUS_CANCELLED) {
      Yii::$app->session->setFlash(
        'info',
        'PurchaseOrder is already cancelled.',
      );
      return $this->redirect(['view', 'id' => $model->id]);
    }

    // Reverse inventory for all items
    $items = PurchaseOrderItem::findAll(['purchase_order_id' => $model->id]);
    foreach ($items as $item) {
      Yii::$app->utils::updateInventory(
        $item->product_id,
        $item->quantity,
        $model->id,
        Inventory::TYPE_PURCHASE_ORDER,
        'out',
      );
    }

    $model->status = PurchaseOrder::STATUS_CANCELLED;
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
        'PurchaseOrder cancelled successfully.',
      );
      return $this->redirect(['view', 'id' => $model->id]);
    }

    Yii::$app->session->setFlash('error', 'Failed to cancel PurchaseOrder.');
    return $this->redirect(['view', 'id' => $model->id]);
  }

  protected function preparePurchaseOrderViewData(
    PurchaseOrder $model,
    PurchaseOrderPayment $paymentModel = null,
  ) {
    $outlet = \app\models\Outlet::find()->one();
    $activities = $this->loadPurchaseOrderActivities($model->id);
    if ($paymentModel === null) {
      $paymentModel = new PurchaseOrderPayment(['date' => date('Y-m-d')]);
      $paymentModel->purchase_order_id = $model->id;
    }
    if ($paymentModel->amount === null) {
      $paymentModel->amount = (float) $model->balance_amount;
    }
    if (!$paymentModel->date) {
      $paymentModel->date = date('Y-m-d');
    }

    $payments = PurchaseOrderPayment::find()
      ->where(['purchase_order_id' => $model->id])
      ->orderBy(['date' => SORT_DESC, 'id' => SORT_DESC])
      ->all();

    return [
      'model' => $model,
      'outlet' => $outlet,
      'activities' => $activities,
      'payments' => $payments,
      'paymentModel' => $paymentModel,
      'paymentMethods' => $this->getPaymentMethodList(),
    ];
  }

  protected function loadPurchaseOrderActivities($id)
  {
    $query = ActivityLog::find()
      ->where([
        'controller' => 'purchase-order',
      ])
      ->andWhere([
        'in',
        'action',
        ['create', 'update', 'delete', 'cancel', 'payment', 'duplicate'],
      ])
      ->andWhere([
        'or',
        ['like', 'params', '"id":' . $id],
        ['like', 'params', '"id":"' . $id . '"'],
        ['like', 'params', '"purchase_order_id":' . $id],
        ['like', 'params', '"purchase_order_id":"' . $id . '"'],
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

  protected function generatePurchaseOrderPaymentCode(PurchaseOrder $model)
  {
    $count = (int) PurchaseOrderPayment::find()
      ->where(['purchase_order_id' => $model->id])
      ->count();
    return $model->code . '-PAY-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
  }

  /**
   * Finds the PurchaseOrder model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param int $id ID
   * @return PurchaseOrder the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = PurchaseOrder::findOne(['id' => $id])) !== null) {
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }

  protected function getSuppliers()
  {
    return ArrayHelper::map(Supplier::find()->all(), 'id', 'name');
  }

  protected function getProducts()
  {
    return ArrayHelper::map(Product::find()->all(), 'id', 'name');
  }

  protected function generateCode()
  {
    $last = PurchaseOrder::find()
      ->orderBy(['id' => SORT_DESC])
      ->one();
    $num = $last ? (int) substr($last->code, 3) + 1 : 1;
    return 'PO-' . str_pad($num, 5, '0', STR_PAD_LEFT);
  }

  protected function generateSerialCode()
  {
    return date('Ymd') .
      '-' .
      strtoupper(Yii::$app->security->generateRandomString(4));
  }

  protected function generatePurchaseOrderCode()
  {
    $last = PurchaseOrder::find()
      ->orderBy(['id' => SORT_DESC])
      ->one();
    $num = 1;
    if ($last && preg_match('/^PO-(\d+)$/', $last->code, $matches)) {
      $num = (int) $matches[1] + 1;
    }
    return 'PO-' . str_pad($num, 5, '0', STR_PAD_LEFT);
  }

  protected function generatePurchaseOrderSerialCode()
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
