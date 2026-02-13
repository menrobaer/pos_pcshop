<?php

namespace app\controllers;

use app\models\ProductBrand;
use app\models\ProductBrandSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ProductBrandController implements the CRUD actions for ProductBrand model.
 */
class ProductBrandController extends Controller
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
   * Lists all ProductBrand models.
   */
  public function actionIndex()
  {
    $searchModel = new ProductBrandSearch();
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
   * Creates a new ProductBrand model.
   */
  public function actionCreate()
  {
    $model = new ProductBrand();

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
        Yii::$app->session->setFlash('success', 'Brand Saved Successfully');
        return $this->redirect(Yii::$app->request->referrer);
      }
    }

    return $this->renderAjax('_form', [
      'model' => $model,
    ]);
  }

  /**
   * Updates an existing ProductBrand model.
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
        Yii::$app->session->setFlash('success', 'Brand Updated Successfully');
        return $this->redirect(Yii::$app->request->referrer);
      }
    }

    return $this->renderAjax('_form', [
      'model' => $model,
    ]);
  }

  /**
   * Deletes an existing ProductBrand model.
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
        'message' => 'Brand deleted successfully.',
      ]);
    }

    Yii::$app->session->setFlash('success', 'Brand Deleted Successfully');
    return $this->redirect(['index']);
  }

  protected function findModel($id)
  {
    if (($model = ProductBrand::findOne(['id' => $id])) !== null) {
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }

  public function actionImportCsv()
  {
    // Handle POST request (file upload)
    if (Yii::$app->request->isPost) {
      $file = \yii\web\UploadedFile::getInstanceByName('csv_file');

      if (!$file) {
        Yii::$app->session->setFlash('error', 'No file uploaded');
        return $this->redirect(Yii::$app->request->referrer);
      }

      if ($file->extension !== 'csv') {
        Yii::$app->session->setFlash('error', 'Only CSV files are allowed');
        return $this->redirect(Yii::$app->request->referrer);
      }

      try {
        $count = 0;
        if (($handle = fopen($file->tempName, 'r')) !== false) {
          fgetcsv($handle); // Skip header
          while (($row = fgetcsv($handle)) !== false) {
            $brand = new ProductBrand();
            $brand->name = $row[0] ?? '';
            if ($brand->save()) {
              $count++;
            }
          }
          fclose($handle);
        }

        try {
          Yii::$app->utils::insertActivityLog([
            'params' => [
              'file' => $file->name,
              'count' => $count,
            ],
          ]);
        } catch (\Throwable $e) {
          // do not block request on logging failure
        }

        Yii::$app->session->setFlash('success', "Successfully imported $count brands");
        return $this->redirect(Yii::$app->request->referrer);
      } catch (\Exception $e) {
        Yii::$app->session->setFlash('error', $e->getMessage());
        return $this->redirect(Yii::$app->request->referrer);
      }
    }

    // Render form for GET request
    return $this->renderAjax('_import');
  }
}
