<?php

namespace app\controllers;

use app\models\Customer;
use app\models\CustomerSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CustomerController implements the CRUD actions for Customer model.
 */
class CustomerController extends Controller
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
          'delete' => ['POST'],
        ],
      ],
    ]);
  }

  /**
   * Lists all Customer models.
   *
   * @return string
   */
  public function actionIndex()
  {
    $searchModel = new CustomerSearch();
    if (empty($this->request->queryParams)) {
      $searchModel->status = 1;
    }
    $dataProvider = $searchModel->search($this->request->queryParams);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
    ]);
  }

  /**
   * Creates a new Customer model.
   * @return string|\yii\web\Response
   */
  public function actionCreate()
  {
    $model = new Customer();

    if ($model->load(Yii::$app->request->post())) {
      if ($model->save()) {
        try {
          Yii::$app->utils::insertActivityLog([
            'params' => array_merge(Yii::$app->request->post(), [
              'id' => $model->id,
            ]),
          ]);
        } catch (\Throwable $e) {
          // do not block request on logging failure
        }
        Yii::$app->session->setFlash('success', 'Customer Saved Successfully');
        return $this->redirect(Yii::$app->request->referrer);
      } else {
        Yii::$app->session->setFlash('warning', 'Failed to save customer.');
      }
    }

    return $this->renderAjax('_form', [
      'model' => $model,
    ]);
  }

  /**
   * Updates an existing Customer model.
   * @param int $id ID
   * @return string|\yii\web\Response
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionUpdate($id)
  {
    $model = $this->findModel($id);

    if ($model->load(Yii::$app->request->post())) {
      if ($model->save()) {
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
          'Customer Updated Successfully',
        );
        return $this->redirect(Yii::$app->request->referrer);
      } else {
        Yii::$app->session->setFlash('warning', 'Failed to update customer.');
      }
    }

    return $this->renderAjax('_form', [
      'model' => $model,
    ]);
  }

  /**
   * Deletes an existing Customer model.
   * @param int $id ID
   * @return \yii\web\Response
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionDelete($id)
  {
    $model = $this->findModel($id);
    try {
      Yii::$app->utils::insertActivityLog([
        'params' => array_merge(Yii::$app->request->post(), [
          'id' => $model->id,
        ]),
      ]);
    } catch (\Throwable $e) {
      // do not block request on logging failure
    }
    $model->delete();

    if (Yii::$app->request->isAjax) {
      return $this->asJson([
        'success' => true,
        'message' => 'Customer deleted successfully.',
      ]);
    }

    Yii::$app->session->setFlash('success', 'Customer Deleted Successfully');
    return $this->redirect(['index']);
  }

  protected function findModel($id)
  {
    if (($model = Customer::findOne(['id' => $id])) !== null) {
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }
}
