<?php

namespace app\controllers;

use app\models\ProductModel;
use app\models\ProductModelSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ProductModelController implements the CRUD actions for ProductModel model.
 */
class ProductModelController extends Controller
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
   * Lists all ProductModel models.
   */
  public function actionIndex()
  {
    $searchModel = new ProductModelSearch();
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
   * Creates a new ProductModel model.
   */
  public function actionCreate()
  {
    $model = new ProductModel();

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
        Yii::$app->session->setFlash('success', 'Category Saved Successfully');
        return $this->redirect(Yii::$app->request->referrer);
      }
    }

    return $this->renderAjax('_form', [
      'model' => $model,
    ]);
  }

  /**
   * Updates an existing ProductModel model.
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
          'Category Updated Successfully',
        );
        return $this->redirect(Yii::$app->request->referrer);
      }
    }

    return $this->renderAjax('_form', [
      'model' => $model,
    ]);
  }

  /**
   * Deletes an existing ProductModel model.
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
        'message' => 'Category deleted successfully.',
      ]);
    }

    Yii::$app->session->setFlash('success', 'Category Deleted Successfully');
    return $this->redirect(['index']);
  }

  protected function findModel($id)
  {
    if (($model = ProductModel::findOne(['id' => $id])) !== null) {
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }
}
