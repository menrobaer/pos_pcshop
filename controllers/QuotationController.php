<?php

namespace app\controllers;

use app\models\Inventory;
use app\models\Quotation;
use app\models\QuotationItem;
use app\models\QuotationSearch;
use app\models\Invoice;
use app\models\InvoiceItem;
use app\models\Customer;
use app\models\Product;
use app\models\ActivityLog;
use Exception;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

/**
 * QuotationController implements the CRUD actions for Quotation model.
 */
class QuotationController extends Controller
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

  public function beforeAction($action)
  {
    if (!parent::beforeAction($action)) {
      return false;
    }

    return true;
  }

  /**
   * Lists all Quotation models.
   *
   * @return string
   */
  public function actionIndex()
  {
    $searchModel = new QuotationSearch();
    $dataProvider = $searchModel->search($this->request->queryParams);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
      'customers' => $this->getCustomers(),
    ]);
  }

  /**
   * Displays a single Quotation model.
   * @param int $id ID
   * @return string
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionView($id)
  {
    $model = $this->findModel($id);
    $outlet = \app\models\Outlet::find()->one();

    // fetch activity logs related to this quotation (params contains "id":<id>)
    // show only mutation actions to keep the activity list concise
    $query = ActivityLog::find()
      ->where(['controller' => 'quotation'])
      ->andWhere([
        'in',
        'action',
        ['create', 'update', 'delete', 'cancel', 'convert-to-invoice'],
      ])
      ->andWhere([
        'or',
        ['like', 'params', '"id":' . $id],
        ['like', 'params', '"id":"' . $id . '"'],
      ]);
    $activities = $query
      ->orderBy(['created_at' => SORT_DESC])
      ->limit(50)
      ->all();

    return $this->render('view', [
      'model' => $model,
      'outlet' => $outlet,
      'activities' => $activities,
    ]);
  }

  /**
   * Creates a new Quotation model.
   * If creation is successful, the browser will be redirected to the 'index' page.
   * @return string|\yii\web\Response
   */
  public function actionCreate()
  {
    $model = new Quotation();
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
          $model->status = Quotation::STATUS_ACTIVE;
          if (!$model->save()) {
            throw new Exception('Failed to save quotation header.');
          }

          $items = $this->request->post('QuotationItem', []);
          foreach ($items as $itemData) {
            $item = new QuotationItem();
            $item->quotation_id = $model->id;
            if ($item->load($itemData, '')) {
              if (!$item->save()) {
                $errors = implode(
                  '<br>',
                  \yii\helpers\ArrayHelper::getColumn($item->getErrors(), 0),
                );
                throw new Exception(
                  'Failed to save quotation item: ' . $errors,
                );
              }
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
            'Quotation created successfully.',
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
   * Updates an existing Quotation model.
   * If update is successful, the browser will be redirected to the 'index' page.
   * @param int $id ID
   * @return string|\yii\web\Response
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionUpdate($id)
  {
    $model = $this->findModel($id);

    if (
      $model->status == Quotation::STATUS_CANCELLED ||
      $model->status == Quotation::STATUS_ACCEPTED
    ) {
      Yii::$app->session->setFlash(
        'error',
        'This quotation cannot be updated after it is cancelled or converted.',
      );
      return $this->redirect(['view', 'id' => $model->id]);
    }

    if ($this->request->isPost && $model->load($this->request->post())) {
      $transaction = Yii::$app->db->beginTransaction();
      try {
        $model->paid_amount = 0;
        $model->balance_amount = $model->grand_total;
        if (!$model->save()) {
          throw new Exception('Failed to update quotation header.');
        }

        QuotationItem::deleteAll(['quotation_id' => $model->id]);
        $items = $this->request->post('QuotationItem', []);
        foreach ($items as $itemData) {
          $item = new QuotationItem();
          $item->quotation_id = $model->id;
          if ($item->load($itemData, '')) {
            if (!$item->save()) {
              $errors = implode(
                '<br>',
                \yii\helpers\ArrayHelper::getColumn($item->getErrors(), 0),
              );
              throw new Exception('Failed to save quotation item: ' . $errors);
            }
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
          'Quotation updated successfully.',
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
   * Deletes an existing Quotation model.
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
      QuotationItem::deleteAll(['quotation_id' => $model->id]);
      $model->status = Quotation::STATUS_DELETED;
      $model->save(false);
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
        'Quotation deleted successfully.',
      );
    } catch (Exception $e) {
      $transaction->rollBack();
      Yii::$app->session->setFlash('error', 'Failed to delete quotation.');
    }

    return $this->redirect(['index']);
  }

  /**
   * Duplicates a quotation into a new, unsaved record.
   * @param int $id ID
   * @return string
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionDuplicate($id)
  {
    $model = $this->findModel($id);

    $duplicate = new Quotation();
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
    $duplicate->status = Quotation::STATUS_ACTIVE;

    $items = [];
    foreach ($model->items as $item) {
      $duplicateItem = new QuotationItem();
      $duplicateItem->attributes = $item->attributes;
      $duplicateItem->id = null;
      $duplicateItem->quotation_id = null;
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
   * Cancels an existing Quotation model.
   * @param int $id ID
   * @return \yii\web\Response
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionCancel($id)
  {
    $model = $this->findModel($id);

    if ($model->status == Quotation::STATUS_CANCELLED) {
      Yii::$app->session->setFlash('info', 'Quotation is already cancelled.');
      return $this->redirect(['view', 'id' => $model->id]);
    }

    $model->status = Quotation::STATUS_CANCELLED;
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
        'Quotation cancelled successfully.',
      );
      return $this->redirect(['view', 'id' => $model->id]);
    }

    Yii::$app->session->setFlash('error', 'Failed to cancel quotation.');
    return $this->redirect(['view', 'id' => $model->id]);
  }

  /**
   * Converts a quotation to a invoice (invoice).
   * @param int $id ID
   * @return \yii\web\Response
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionConvertToInvoice($id)
  {
    $model = $this->findModel($id);

    $existingInvoice = Invoice::find()
      ->where(['quotation_id' => $model->id])
      ->one();
    if ($existingInvoice) {
      Yii::$app->session->setFlash(
        'info',
        'Invoice already exists for this quotation.',
      );
      return $this->redirect(['invoice/view', 'id' => $existingInvoice->id]);
    }

    return $this->redirect(['invoice/create', 'quotation_id' => $model->id]);
  }

  /**
   * Finds the Quotation model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param int $id ID
   * @return Quotation the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = Quotation::findOne(['id' => $id])) !== null) {
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
    return ArrayHelper::map(Product::find()->andWhere(['!=', 'status', Product::STATUS_DELETED])->all(), 'id', 'name');
  }

  protected function generateCode()
  {
    $last = Quotation::find()
      ->orderBy(['id' => SORT_DESC])
      ->one();
    $num = $last ? (int) substr($last->code, 3) + 1 : 1;
    return 'QT-' . str_pad($num, 5, '0', STR_PAD_LEFT);
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
