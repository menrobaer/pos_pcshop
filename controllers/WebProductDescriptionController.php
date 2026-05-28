<?php

namespace app\controllers;

use app\models\website\ProductDescriptionType;
use app\models\website\ProductDescriptionTypeSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * WebProductDescriptionController implements the CRUD actions for ProductDescriptionType model.
 */
class WebProductDescriptionController extends Controller
{
  /**
   * @inheritDoc
   */
  public function behaviors()
  {
    return array_merge(
      parent::behaviors(),
      [
        // 'access' => [
        //   'class' => \yii\filters\AccessControl::class,
        //   'rules' => [
        //     [
        //       'actions' => \app\models\User::getUserPermission(Yii::$app->controller->id),
        //       'allow' => true,
        //     ]
        //   ],
        // ],
        'verbs' => [
          'class' => VerbFilter::class,
          'actions' => [
            'delete' => ['POST'],
          ],
        ],
      ]
    );
  }

  public function getViewPath()
  {
    return Yii::getAlias('@app/views/w-product-description');
  }

  /**
   * Lists all ProductDescriptionType models.
   */
  public function actionIndex()
  {
    $searchModel = new ProductDescriptionTypeSearch();
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
   * Creates a new ProductDescriptionType model.
   */
  public function actionCreate()
  {
    $model = new ProductDescriptionType();
    $model->status = 1;

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
        Yii::$app->session->setFlash('success', 'Product Description Saved Successfully');
        return $this->redirect(Yii::$app->request->referrer);
      }
    }

    return $this->renderAjax('_form', [
      'model' => $model,
    ]);
  }

  /**
   * Updates an existing ProductDescriptionType model.
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
   * Deletes an existing ProductDescriptionType model.
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
    $model->status = ProductDescriptionType::STATUS_DELETED;
    $model->save(false);

    if (Yii::$app->request->isAjax) {
      return $this->asJson([
        'success' => true,
        'message' => 'Product Description deleted successfully.',
      ]);
    }

    Yii::$app->session->setFlash('success', 'Product Description Deleted Successfully');
    return $this->redirect(['index']);
  }

  protected function findModel($id)
  {
    if (($model = ProductDescriptionType::findOne(['id' => $id])) !== null) {
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }
}
