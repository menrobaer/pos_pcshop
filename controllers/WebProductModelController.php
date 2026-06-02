<?php

namespace app\controllers;

use app\models\website\ProductModel;
use app\models\website\ProductModelSearch;
use app\models\website\ProductBrand;
use Yii;
use yii\base\Exception;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
/**
 * WebProductModelController implements the CRUD actions for ProductModel model.
 */
class WebProductModelController extends Controller
{
  protected function getBrands()
  {
    return ArrayHelper::map(
      ProductBrand::find()
        ->orderBy(['name' => SORT_ASC])
        ->all(),
      'id',
      'name',
    );
  }

  protected function getWebsiteDb()
  {
    return ProductModel::getDb();
  }

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
            'dependent' => ['POST'],
          ],
        ],
      ]
    );
  }

  public function getViewPath()
  {
    return Yii::getAlias('@app/views/w-product-model');
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
      'brands' => $this->getBrands(),
    ]);
  }

  /**
   * Creates a new ProductModel model.
   */
  public function actionCreate()
  {
    $model = new ProductModel();
    $model->status = 1;

    if ($model->load(Yii::$app->request->post())) {
      $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
      if ($model->imageFile) {
        if ($path = $model->uploadImage()) {
          $model->image_url = $path;
        }
      }
      $model->imageFile = null;
      if (empty($model->sort)) {
        $maxSort = ProductModel::find()->max('sort');
        $model->sort = ((int) $maxSort) + 1;
      }
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
        Yii::$app->session->setFlash('success', 'Model Saved Successfully');
        return $this->redirect(Yii::$app->request->referrer);
      }
    }

    return $this->renderAjax('_form', [
      'model' => $model,
      'brands' => $this->getBrands(),
    ]);
  }

  /**
   * Updates an existing ProductModel model.
   */
  public function actionUpdate($id)
  {
    $model = $this->findModel($id);

    if ($model->load(Yii::$app->request->post())) {
      $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
      if ($model->imageFile) {
        if ($path = $model->uploadImage()) {
          $model->image_url = $path;
        }
      }
      $model->imageFile = null;
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
          'Model Updated Successfully',
        );
        return $this->redirect(Yii::$app->request->referrer);
      }
    }

    return $this->renderAjax('_form', [
      'model' => $model,
      'brands' => $this->getBrands(),
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
    $model->status = ProductModel::STATUS_DELETED;
    $model->save(false);

    if (Yii::$app->request->isAjax) {
      return $this->asJson([
        'success' => true,
        'message' => 'Model deleted successfully.',
      ]);
    }

    Yii::$app->session->setFlash('success', 'Model Deleted Successfully');
    return $this->redirect(['index']);
  }

  public function actionDependent()
  {
    if (!$this->request->isAjax || $this->request->post('action') !== 'update_order') {
      return $this->asJson(['status' => 'failed']);
    }

    $brandId = (int) $this->request->post('brand_id', 0);
    if ($brandId < 1) {
      return $this->asJson(['status' => 'failed']);
    }

    $orderArr = (array) Yii::$app->request->post('orderArr', []);
    if (empty($orderArr)) {
      return $this->asJson(['status' => 'failed']);
    }

    $transaction = $this->getWebsiteDb()->beginTransaction();
    try {
      foreach ($orderArr as $index => $id) {
        $model = ProductModel::findOne((int) $id);
        if (!$model) {
          throw new Exception('Model not found: ' . $id);
        }
        if ($model->brand_id === null) {
          throw new Exception('Model brand_id is null: ' . $id);
        }
        if ((int) $model->brand_id !== $brandId) {
          throw new Exception('Invalid brand scope for model: ' . $id);
        }
        $model->sort = $index + 1;
        if (!$model->save(false, ['sort'])) {
          throw new Exception('Failed to save sort: ' . $id);
        }
      }

      $transaction->commit();
      return $this->asJson(['status' => 'saved']);
    } catch (Exception $e) {
      $transaction->rollBack();
      return $this->asJson(['status' => 'failed']);
    }
  }

  protected function findModel($id)
  {
    if (($model = ProductModel::findOne(['id' => $id])) !== null) {
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }
}
