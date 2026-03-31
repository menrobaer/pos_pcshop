<?php

namespace app\controllers;

use app\models\Service;
use app\models\ServiceItem;
use app\models\ServicePayment;
use app\models\ServiceSearch;
use app\models\PaymentMethod;
use Exception;
use League\Glide\Server;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * ServiceController provides CRUD pages for services.
 */
class ServiceController extends Controller
{
  /**
   * @inheritDoc
   */
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

  /**
   * Lists all Service models.
   * @return string
   */
  public function actionIndex()
  {
    $searchModel = new ServiceSearch();
    $dataProvider = $searchModel->search($this->request->queryParams);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
      'customers' => $this->getCustomers(),
    ]);
  }

  /**
   * Displays a single Service model.
   * @param int $id ID
   * @return string
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionView($id)
  {
    $model = $this->findModel($id);
    return $this->render('view', $this->prepareServiceViewData($model));
  }

  /**
   * Creates a new Service model.
   * If creation is successful, the browser will be redirected to the 'view' page.
   * @return string|\yii\web\Response
   */
  public function actionCreate()
  {
    $model = new Service();
    $model->date = date('Y-m-d');
    $model->due_date = date('Y-m-d', strtotime('+7 days'));
    $model->code = $this->generateCode();
    $model->serial_code = $this->generateSerialCode();
    $items = [];

    if ($this->request->isPost) {
      if ($model->load($this->request->post())) {
        $transaction = Yii::$app->db->beginTransaction();
        try {
          $model->paid_amount = 0;
          $model->balance_amount = $model->grand_total;
          $model->status = Service::STATUS_ACTIVE;
          if (!$model->save()) {
            throw new Exception('Failed to save Service header.');
          }

          $items = $this->request->post('ServiceItem', []);
          $costTotal = 0;
          foreach ($items as $itemData) {
            $item = new ServiceItem();
            $item->service_id = $model->id;
            if ($item->load($itemData, '')) {
              $costTotal += $item->cost * $item->quantity;
              if (!$item->save()) {
                $errors = implode(
                  '<br>',
                  ArrayHelper::getColumn($item->getErrors(), 0),
                );
                throw new Exception('Failed to save Service item: ' . $errors);
              }
            }
          }
          $model->cost_total = $costTotal;
          if (!$model->save()) {
            throw new Exception('Failed to save Service cost.');
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
            'Service created successfully.',
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
      'items' => $items,
      'customers' => $this->getCustomers(),
    ]);
  }

  /**
   * Updates an existing Service model.
   * If update is successful, the browser will be redirected to the 'view' page.
   * @param int $id ID
   * @return string|\yii\web\Response
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionUpdate($id)
  {
    $model = $this->findModel($id);

    if (
      $model->status == Service::STATUS_CANCELLED ||
      $model->status == Service::STATUS_PAID
    ) {
      Yii::$app->session->setFlash(
        'error',
        'This Service cannot be updated after it is cancelled or paid.',
      );
      return $this->redirect(['view', 'id' => $model->id]);
    }

    if ($this->request->isPost && $model->load($this->request->post())) {
      $transaction = Yii::$app->db->beginTransaction();
      try {
        $model->paid_amount = 0;
        $model->balance_amount = $model->grand_total;
        if (!$model->save()) {
          throw new Exception('Failed to update Service header.');
        }

        ServiceItem::deleteAll(['service_id' => $model->id]);
        $items = $this->request->post('ServiceItem', []);
        $costTotal = 0;
        foreach ($items as $itemData) {
          $item = new ServiceItem();
          $item->service_id = $model->id;
          if ($item->load($itemData, '')) {
            $costTotal += $item->cost * $item->quantity;
            if (!$item->save()) {
              $errors = implode(
                '<br>',
                ArrayHelper::getColumn($item->getErrors(), 0),
              );
              throw new Exception('Failed to save Service item: ' . $errors);
            }
          }
        }
        $model->cost_total = $costTotal;
        if (!$model->save()) {
          throw new Exception('Failed to save Service cost.');
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
          'Service updated successfully.',
        );
        return $this->redirect(['view', 'id' => $model->id]);
      } catch (Exception $e) {
        $transaction->rollBack();
        Yii::$app->session->setFlash('error', $e->getMessage());
      }
    }

    return $this->render('update', [
      'model' => $model,
      'items' => $model->items,
      'customers' => $this->getCustomers(),
    ]);
  }


  public function actionAddPayment($id)
  {
    $service = $this->findModel($id);

    if ($service->status == Service::STATUS_CANCELLED) {
      Yii::$app->session->setFlash(
        'error',
        'Cannot record payment for a cancelled service.',
      );
      return $this->redirect(['view', 'id' => $service->id]);
    }

    $paymentModel = new ServicePayment(['date' => date('Y-m-d')]);
    $paymentModel->service_id = $service->id;

    if ($this->request->isPost && $paymentModel->load($this->request->post())) {
      $paymentModel->code = $this->generatePaymentCode($service);
      $paymentModel->created_at = date('Y-m-d H:i:s');
      $paymentModel->created_by = Yii::$app->user->id;
      $paymentModel->amount = round((float) $paymentModel->amount, 2);
      $balance = round((float) $service->balance_amount, 2);

      if ($balance <= 0) {
        $paymentModel->addError('amount', 'Service is already fully paid.');
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

          $service->paid_amount = round(
            $service->paid_amount + $paymentModel->amount,
            2,
          );
          $service->balance_amount = max(
            0,
            round($service->grand_total - $service->paid_amount, 2),
          );
          if ($service->balance_amount <= 0) {
            $service->status = Service::STATUS_PAID;
            $service->balance_amount = 0;
          } elseif ($service->status !== Service::STATUS_PROCESS) {
            $service->status = Service::STATUS_PROCESS;
          }

          if (
            !$service->save(false, ['paid_amount', 'balance_amount', 'status'])
          ) {
            throw new Exception('Failed to sync service totals.');
          }

          $transaction->commit();
          try {
            Yii::$app->utils::insertActivityLog([
              'action' => 'payment',
              'params' => [
                'service_id' => $service->id,
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
          return $this->redirect(['view', 'id' => $service->id]);
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
      $this->prepareServiceViewData($service),
    );
  }

  /**
   * Deletes an existing Service model.
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
      $model->status = Service::STATUS_DELETED;
      if (!$model->save(false, ['status'])) {
        throw new Exception('Failed to delete Service.');
      }
      $transaction->commit();

      try {
        Yii::$app->utils::insertActivityLog([
          'action' => 'delete',
          'params' => ['id' => $model->id],
        ]);
      } catch (\Throwable $e) {
        // do not block request on logging failure
      }

      Yii::$app->session->setFlash('success', 'Service deleted successfully.');
    } catch (Exception $e) {
      $transaction->rollBack();
      Yii::$app->session->setFlash('error', 'Failed to delete Service.');
    }

    return $this->redirect(['index']);
  }

  /**
   * Finds the Service model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param int $id ID
   * @return Service the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = Service::findOne(['id' => $id])) !== null) {
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }


  protected function generateCode()
  {
    $last = Service::find()
      ->orderBy(['id' => SORT_DESC])
      ->one();
    $num = $last ? (int) substr($last->code, 4) + 1 : 1;
    // Reset every 30
    $num = (($num - 1) % 30) + 1;
    return 'SER-' . str_pad($num, 5, '0', STR_PAD_LEFT);
  }

  protected function generateSerialCode()
  {
    return date('Ymd') . '-' . strtoupper(Yii::$app->security->generateRandomString(4));
  }


  /**
   * Get list of customers for dropdown
   * @return array
   */
  private function getCustomers()
  {
    return ArrayHelper::map(
      \app\models\Customer::find()->orderBy(['name' => SORT_ASC])->all(),
      'id',
      'name',
    );
  }

  /**
   * Prepare data for service view
   */
  private function prepareServiceViewData($model)
  {
    $outlet = \app\models\Outlet::find()->one();
    $paymentModel = new ServicePayment(['date' => date('Y-m-d')]);
    $paymentModel->service_id = $model->id;
    if ($paymentModel->amount === null) {
      $paymentModel->amount = (float) $model->balance_amount;
    }
    if (!$paymentModel->date) {
      $paymentModel->date = date('Y-m-d');
    }

    $payments = ServicePayment::find()
      ->where(['service_id' => $model->id])
      ->orderBy(['date' => SORT_DESC, 'id' => SORT_DESC])
      ->all();

    return [
      'model' => $model,
      'outlet' => $outlet,
      'payments' => $payments,
      'paymentModel' => $paymentModel,
      'paymentMethods' => $this->getPaymentMethodList(),
    ];
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

  protected function generatePaymentCode(Service $service)
  {
    $count = (int) ServicePayment::find()
      ->where(['service_id' => $service->id])
      ->count();
    return $service->code . '-PAY-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
  }
}
