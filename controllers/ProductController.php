<?php

namespace app\controllers;

use app\models\Product;
use app\models\ProductBrand;
use app\models\ProductCategory;
use app\models\ProductModel;
use app\models\ProductSearch;
use app\models\ProductVariation;
use Exception;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends Controller
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

  protected function getCategories()
  {
    return ArrayHelper::map(
      ProductCategory::find()
        ->orderBy(['name' => SORT_ASC])
        ->all(),
      'id',
      'name',
    );
  }

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

  protected function getModels()
  {
    return ArrayHelper::map(
      ProductModel::find()
        ->orderBy(['name' => SORT_ASC])
        ->all(),
      'id',
      'name',
    );
  }

  /**
   * Lists all Product models.
   *
   * @return string
   */
  public function actionIndex()
  {
    $searchModel = new ProductSearch();
    if (empty($this->request->queryParams)) {
      $searchModel->status = 1;
    }
    $dataProvider = $searchModel->search($this->request->queryParams);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
      'categories' => $this->getCategories(),
      'brands' => $this->getBrands(),
    ]);
  }

  /**
   * Displays a single Product model.
   * @param int $id ID
   * @return string
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionView($id)
  {
    $modelPO = new \app\models\PurchaseOrder();
    $modelPO->date = date('Y-m-d');

    // Generate PO Code
    $last = \app\models\PurchaseOrder::find()
      ->orderBy(['id' => SORT_DESC])
      ->one();
    $num = $last ? (int) substr($last->code, 3) + 1 : 1;
    $modelPO->code = 'PO-' . str_pad($num, 5, '0', STR_PAD_LEFT);

    // Generate Serial Code
    $modelPO->serial_code = date('Ymd') . '-' . strtoupper(Yii::$app->security->generateRandomString(4));

    $suppliers = ArrayHelper::map(
      \app\models\Supplier::find()
        ->orderBy(['name' => SORT_ASC])
        ->all(),
      'id',
      'name',
    );
    return $this->render('view', [
      'model' => $this->findModel($id),
      'modelPO' => $modelPO,
      'suppliers' => $suppliers,
    ]);
  }

  /**
   * Creates a new Product model.
   * If creation is successful, the browser will be redirected to the 'view' page.
   * @return string|\yii\web\Response
   */
  public function actionCreate()
  {
    $model = new Product();

    if ($model->load(Yii::$app->request->post())) {
      $transaction_exception = Yii::$app->db->beginTransaction();
      try {
        $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
        if ($model->imageFile) {
          if ($path = $model->uploadImage()) {
            $model->image = $path;
          }
        }
        $model->imageFile = null;
        if (!$model->save(false)) {
          throw new Exception(
            'Failed to Save #1! Code: ' . json_encode($model->getFirstErrors()),
          );
        }

        $transaction_exception->commit();
        try {
          Yii::$app->utils::insertActivityLog([
            'params' => array_merge(Yii::$app->request->post(), [
              'id' => $model->id,
            ]),
          ]);
        } catch (\Throwable $e) {
          // do not block request on logging failure
        }
        Yii::$app->session->setFlash('success', 'Item Saved Successfully');
        return $this->redirect(['view', 'id' => $model->id]);
      } catch (Exception $ex) {
        Yii::$app->session->setFlash('warning', $ex->getMessage());
        $transaction_exception->rollBack();
        print_r($ex->getMessage());
        exit();
        return $this->redirect(Yii::$app->request->referrer);
      }
    }

    return $this->renderAjax('_form', [
      'model' => $model,
      'categories' => $this->getCategories(),
      'brands' => $this->getBrands(),
      'models' => $this->getModels(),
    ]);
  }

  /**
   * Updates an existing Product model.
   * If update is successful, the browser will be redirected to the 'view' page.
   * @param int $id ID
   * @return string|\yii\web\Response
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionUpdate($id)
  {
    $model = $this->findModel($id);

    if ($model->load(Yii::$app->request->post())) {
      $transaction_exception = Yii::$app->db->beginTransaction();
      try {
        $oldImage = $model->image;
        if ($oldImage && file_exists($oldImage)) {
          unlink($oldImage);
        }

        $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
        if ($model->imageFile) {
          if ($path = $model->uploadImage()) {
            $model->image = $path;
          }
        }
        $model->imageFile = null;
        if (!$model->save()) {
          throw new Exception('Failed to Save! Code #001');
        }

        $transaction_exception->commit();
        try {
          Yii::$app->utils::insertActivityLog([
            'params' => array_merge(Yii::$app->request->post(), [
              'id' => $model->id,
            ]),
          ]);
        } catch (\Throwable $e) {
          // do not block request on logging failure
        }
        Yii::$app->session->setFlash('success', 'Item Saved Successfully');
        return $this->redirect(['view', 'id' => $model->id]);
      } catch (Exception $ex) {
        Yii::$app->session->setFlash('warning', $ex->getMessage());
        $transaction_exception->rollBack();
        return $this->redirect(Yii::$app->request->referrer);
      }
    }

    return $this->renderAjax('_form', [
      'model' => $model,
      'categories' => $this->getCategories(),
      'brands' => $this->getBrands(),
      'models' => $this->getModels(),
    ]);
  }

  /**
   * Deletes an existing Product model.
   * If deletion is successful, the browser will be redirected to the 'index' page.
   * @param int $id ID
   * @return \yii\web\Response
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionDelete($id)
  {
    $model = $this->findModel($id);

    // Check if product has inventory records
    if ($model->getInventories()->count() > 0 && 0 === 1) {
      if (Yii::$app->request->isAjax) {
        return $this->asJson([
          'success' => false,
          'message' =>
          'Cannot delete product: It has been used in transactions and has inventory records.',
        ]);
      }

      Yii::$app->session->setFlash(
        'error',
        'Cannot delete product: It has been used in transactions and has inventory records.',
      );
      return $this->redirect(['view', 'id' => $model->id]);
    }

    try {
      Yii::$app->utils::insertActivityLog([
        'params' => array_merge(Yii::$app->request->post(), [
          'id' => $model->id,
        ]),
      ]);
    } catch (\Throwable $e) {
      // do not block request on logging failure
    }

    $model->status = Product::STATUS_DELETED;
    $model->save(false);

    if (Yii::$app->request->isAjax) {
      return $this->asJson([
        'success' => true,
        'message' => 'Product deleted successfully.',
      ]);
    }

    Yii::$app->session->setFlash('success', 'Product Deleted Successfully');
    return $this->redirect(['index']);
  }

  /**
   * Deletes a ProductVariation model.
   * If deletion is successful, the browser will be redirected to the product 'view' page.
   * @param int $id ID
   * @return \yii\web\Response
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionDeleteVariation($id)
  {
    $variation = ProductVariation::findOne($id);
    if (!$variation) {
      throw new NotFoundHttpException('The requested variation does not exist.');
    }

    $productId = $variation->product_id;

    try {
      Yii::$app->utils::insertActivityLog([
        'params' => [
          'action' => 'delete_variation',
          'id' => $id,
          'product_id' => $productId,
          'serial' => $variation->serial,
        ],
      ]);
    } catch (\Throwable $e) {
      // do not block request on logging failure
    }

    $variation->status = ProductVariation::STATUS_DELETED;
    $variation->save(false);

    Yii::$app->session->setFlash('success', 'Variation deleted successfully.');
    return $this->redirect(['view', 'id' => $productId]);
  }

  protected function findModel($id)
  {
    if (($model = Product::findOne(['id' => $id])) !== null) {
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }
}
