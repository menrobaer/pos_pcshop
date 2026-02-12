<?php

namespace app\controllers;

use app\models\ActivityLog;
use app\models\ExpenseItem;
use app\models\Expense;
use app\models\ExpenseSearch;
use app\models\Supplier;
use Exception;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * ExpenseController provides CRUD operations for expenses.
 */
class ExpenseController extends Controller
{
  /**
   * @inheritDoc
   */
  public function behaviors()
  {
    return array_merge(parent::behaviors(), [
      'verbs' => [
        'class' => VerbFilter::class,
        'actions' => [],
      ],
    ]);
  }

  /**
   * Lists all Expense models.
   *
   * @return string
   */
  public function actionIndex()
  {
    $searchModel = new ExpenseSearch();
    $dataProvider = $searchModel->search($this->request->queryParams);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
      'suppliers' => $this->getSuppliers(),
    ]);
  }

  /**
   * Displays a single Expense model.
   * @param int $id ID
   * @return string
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionView($id)
  {
    $model = $this->findModel($id);
    $outlet = \app\models\Outlet::find()->one();

    // fetch activity logs related to this Expense
    $query = ActivityLog::find()
      ->where(['controller' => 'expense'])
      ->andWhere(['in', 'action', ['create', 'update', 'delete', 'cancel']])
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
   * Creates a new Expense model.
   * If creation is successful, the browser will be redirected to the 'index' page.
   * @return string|\yii\web\Response
   */
  public function actionCreate()
  {
    $model = new Expense();
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
          $model->status = Expense::STATUS_ACTIVE;
          if (!$model->save()) {
            throw new Exception('Failed to save Expense header.');
          }

          $items = $this->request->post('ExpenseItem', []);
          foreach ($items as $itemData) {
            $item = new ExpenseItem();
            $item->expense_id = $model->id;
            if ($item->load($itemData, '')) {
              if (!$item->save()) {
                $errors = implode(
                  '<br>',
                  \yii\helpers\ArrayHelper::getColumn($item->getErrors(), 0),
                );
                throw new Exception('Failed to save Expense item: ' . $errors);
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
            'Expense created successfully.',
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
    ]);
  }

  /**
   * Updates an existing Expense model.
   * If update is successful, the browser will be redirected to the 'index' page.
   * @param int $id ID
   * @return string|\yii\web\Response
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionUpdate($id)
  {
    $model = $this->findModel($id);

    if (
      $model->status == Expense::STATUS_CANCELLED ||
      $model->status == Expense::STATUS_PAID
    ) {
      Yii::$app->session->setFlash(
        'error',
        'This Expense cannot be updated after it is cancelled or paid.',
      );
      return $this->redirect(['view', 'id' => $model->id]);
    }

    if ($this->request->isPost && $model->load($this->request->post())) {
      $transaction = Yii::$app->db->beginTransaction();
      try {
        $model->paid_amount = 0;
        $model->balance_amount = $model->grand_total;
        if (!$model->save()) {
          throw new Exception('Failed to update Expense header.');
        }

        ExpenseItem::deleteAll(['expense_id' => $model->id]);
        $items = $this->request->post('ExpenseItem', []);
        foreach ($items as $itemData) {
          $item = new ExpenseItem();
          $item->expense_id = $model->id;
          if ($item->load($itemData, '')) {
            if (!$item->save()) {
              $errors = implode(
                '<br>',
                \yii\helpers\ArrayHelper::getColumn($item->getErrors(), 0),
              );
              throw new Exception('Failed to save Expense item: ' . $errors);
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
          'Expense updated successfully.',
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
    ]);
  }

  /**
   * Deletes an existing Expense model.
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
      ExpenseItem::deleteAll(['expense_id' => $model->id]);
      $model->delete();
      $transaction->commit();
      try {
        Yii::$app->utils::insertActivityLog([
          'params' => ['id' => $model->id],
        ]);
      } catch (\Throwable $e) {
        // do not block request on logging failure
      }
      Yii::$app->session->setFlash('success', 'Expense deleted successfully.');
    } catch (Exception $e) {
      $transaction->rollBack();
      Yii::$app->session->setFlash('error', 'Failed to delete Expense.');
    }

    return $this->redirect(['index']);
  }

  /**
   * Duplicates an Expense into a new, unsaved record.
   * @param int $id ID
   * @return string
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionDuplicate($id)
  {
    $model = $this->findModel($id);

    $duplicate = new Expense();
    $duplicate->supplier_id = $model->supplier_id;
    $duplicate->date = date('Y-m-d');
    $duplicate->due_date = date('Y-m-d', strtotime('+7 days'));
    $duplicate->code = $this->generateCode();
    $duplicate->serial_code = $this->generateSerialCode();
    $duplicate->remark = $model->remark;
    $duplicate->delivery_fee = $model->delivery_fee;
    $duplicate->extra_charge = $model->extra_charge;
    $duplicate->sub_total = $model->sub_total;
    $duplicate->grand_total = $model->grand_total;
    $duplicate->paid_amount = 0;
    $duplicate->balance_amount = $model->grand_total;
    $duplicate->status = Expense::STATUS_ACTIVE;

    $items = [];
    foreach ($model->items as $item) {
      $duplicateItem = new ExpenseItem();
      $duplicateItem->attributes = $item->attributes;
      $duplicateItem->id = null;
      $duplicateItem->expense_id = null;
      $items[] = $duplicateItem;
    }

    return $this->render('create', [
      'model' => $duplicate,
      'suppliers' => $this->getSuppliers(),
      'items' => $items,
      'isDuplicate' => true,
    ]);
  }

  /**
   * Cancels an existing Expense model.
   * @param int $id ID
   * @return \yii\web\Response
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionCancel($id)
  {
    $model = $this->findModel($id);

    if ($model->status == Expense::STATUS_CANCELLED) {
      Yii::$app->session->setFlash('info', 'Expense is already cancelled.');
      return $this->redirect(['view', 'id' => $model->id]);
    }

    $model->status = Expense::STATUS_CANCELLED;
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
        'Expense cancelled successfully.',
      );
      return $this->redirect(['view', 'id' => $model->id]);
    }

    Yii::$app->session->setFlash('error', 'Failed to cancel Expense.');
    return $this->redirect(['view', 'id' => $model->id]);
  }

  /**
   * Finds the Expense model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param int $id ID
   * @return Expense the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = Expense::findOne(['id' => $id])) !== null) {
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }

  protected function getSuppliers()
  {
    return ArrayHelper::map(Supplier::find()->all(), 'id', 'name');
  }

  protected function generateCode()
  {
    $last = Expense::find()
      ->orderBy(['id' => SORT_DESC])
      ->one();
    $num = $last ? (int) substr($last->code, 4) + 1 : 1;
    return 'EXP-' . str_pad($num, 5, '0', STR_PAD_LEFT);
  }

  protected function generateSerialCode()
  {
    return date('Ymd') .
      '-' .
      strtoupper(Yii::$app->security->generateRandomString(4));
  }
}
