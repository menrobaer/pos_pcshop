<?php

namespace app\controllers;

use app\models\website\Product;
use app\models\website\ProductBrand;
use app\models\website\ProductCategory;
use app\models\website\ProductModel;
use app\models\website\ProductSearch;
use app\models\website\ProductDescription;
use app\models\website\ProductVariation;
use app\models\website\ProductSource;
use app\models\website\Warranty;
use Exception;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * WebProductController implements the CRUD actions for Product model.
 */
class WebProductController extends Controller
{
  public function getViewPath()
  {
    return Yii::getAlias('@app/views/w-product');
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
            'delete-variation' => ['POST'],
            'duplicate-variation' => ['GET', 'POST'],
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

  protected function getSources()
  {
    return ArrayHelper::map(
      ProductSource::find()
        ->orderBy(['name' => SORT_ASC])
        ->all(),
      'id',
      'name',
    );
  }

  protected function getWarranties()
  {
    return ArrayHelper::map(
      Warranty::find()
        ->orderBy(['name' => SORT_ASC])
        ->all(),
      'id',
      'name',
    );
  }

  protected function getConditions()
  {
    return [1=>'New', 2=>'Used'];
  }

  protected function getPostedDescriptions()
  {
    $descriptionPost = Yii::$app->request->post('description', []);
    $typeIds = isset($descriptionPost['type_id']) && is_array($descriptionPost['type_id'])
      ? $descriptionPost['type_id']
      : [];
    $values = isset($descriptionPost['value']) && is_array($descriptionPost['value'])
      ? $descriptionPost['value']
      : [];

    return [$typeIds, $values];
  }

  protected function saveVariationDescriptions($variationId, array $typeIds, array $values)
  {
    ProductDescription::deleteAll(['variation_id' => (int) $variationId]);

    foreach ($typeIds as $index => $typeId) {
      $typeId = (int) $typeId;
      $value = isset($values[$index]) ? trim((string) $values[$index]) : '';
      if ($typeId < 1 || $value === '') {
        continue;
      }

      $description = new ProductDescription();
      $description->variation_id = (int) $variationId;
      $description->type_id = $typeId;
      $description->description = $value;
      $description->status = 1;

      if (!$description->save()) {
        throw new Exception(
          'Failed to Save Variation Description! Code: ' . json_encode($description->getFirstErrors()),
        );
      }
    }
  }

  /**
   * Lists all Product models.
   *
   * @return string
   */
  public function actionIndex()
  {
    $searchModel = new ProductSearch();
    $session = Yii::$app->session;
    $sessionKey = 'webProductSearch';

    // Check if user wants to clear search
    if ($this->request->get('clear')) {
      $session->remove($sessionKey);
      return $this->redirect(['index']);
    }

    if (empty($this->request->queryParams)) {
      // If no query params, try to load from session
      if ($session->has($sessionKey)) {
        $this->request->setQueryParams($session->get($sessionKey));
      } else {
        $searchModel->status = Product::STATUS_ACTIVE;
      }
    } else {
      // Save search params to session
      $session->set($sessionKey, $this->request->queryParams);
    }

    $dataProvider = $searchModel->search($this->request->queryParams);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
      'categories' => $this->getCategories(),
      'brands' => $this->getBrands(),
      'models' => $this->getModels(),
      'hasSearchSession' => $session->has($sessionKey),
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
    $model->status = Product::STATUS_ACTIVE;

    $modelVariation = new ProductVariation();

    if ($model->load(Yii::$app->request->post())
      && $modelVariation->load(Yii::$app->request->post())) {
      $transaction_exception = Product::getDb()->beginTransaction();
      try {
        if (!$model->save(false)) {
          throw new Exception(
            'Failed to Save #1! Code: ' . json_encode($model->getFirstErrors()),
          );
        }

        $modelVariation->product_id = (int) $model->id;
        $modelVariation->imageFile = UploadedFile::getInstance($modelVariation, 'imageFile');
        if ($modelVariation->imageFile) {
          if ($path = $modelVariation->uploadImage()) {
            $modelVariation->image_url = $path;
          } else {
            throw new Exception('Failed to upload variation image to S3. Please check S3 credentials and bucket permissions.');
          }
        }
        $modelVariation->imageFile = null;
        if (!$modelVariation->save(false)) {
          throw new Exception(
            'Failed to Save #2! Code: ' . json_encode($modelVariation->getFirstErrors()),
          );
        }

        [$typeIds, $values] = $this->getPostedDescriptions();
        $this->saveVariationDescriptions($modelVariation->id, $typeIds, $values);

        $transaction_exception->commit();
        try {
          Yii::$app->utils::insertActivityLog([
            'params' => array_merge(Yii::$app->request->post(), [
              'id' => $model->id,
              'variation_id' => $modelVariation->id,
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
      'modelVariation' => $modelVariation,
      'categories' => $this->getCategories(),
      'brands' => $this->getBrands(),
      'models' => $this->getModels(),
      'conditions' => $this->getConditions(),
      'sources' => $this->getSources(),
      'warranties' => $this->getWarranties(),
    ]);
  }

  public function actionCreateVariation($product_id)
  {
    $product = $this->findModel($product_id);
    $modelVariation = new ProductVariation();
    $modelVariation->product_id = (int) $product->id;
    $modelVariation->status = ProductVariation::STATUS_ACTIVE;

    if ($modelVariation->load(Yii::$app->request->post())) {
      $transaction = Product::getDb()->beginTransaction();
      try {
        $modelVariation->product_id = (int) $product->id;
        $modelVariation->imageFile = UploadedFile::getInstance($modelVariation, 'imageFile');
        if ($modelVariation->imageFile) {
          if ($path = $modelVariation->uploadImage()) {
            $modelVariation->image_url = $path;
          } else {
            throw new Exception('Failed to upload variation image to S3. Please check S3 credentials and bucket permissions.');
          }
        }
        $modelVariation->imageFile = null;

        if (!$modelVariation->save(false)) {
          throw new Exception(
            'Failed to Save Variation! Code: ' . json_encode($modelVariation->getFirstErrors()),
          );
        }

        [$typeIds, $values] = $this->getPostedDescriptions();
        $this->saveVariationDescriptions($modelVariation->id, $typeIds, $values);

        $transaction->commit();
        Yii::$app->session->setFlash('success', 'Variation added successfully.');
        return $this->redirect(['view', 'id' => $product->id]);
      } catch (Exception $ex) {
        $transaction->rollBack();
        Yii::$app->session->setFlash('warning', $ex->getMessage());
        return $this->redirect(['view', 'id' => $product->id]);
      }
    }

    return $this->renderAjax('_variation_modal_form', [
      'modelVariation' => $modelVariation,
      'sources' => $this->getSources(),
      'warranties' => $this->getWarranties(),
      'submitLabel' => 'Save Variation',
    ]);
  }

  public function actionUpdateVariation($id)
  {
    $modelVariation = ProductVariation::findOne($id);
    if (!$modelVariation) {
      throw new NotFoundHttpException('The requested variation does not exist.');
    }

    $productId = (int) $modelVariation->product_id;

    if ($modelVariation->load(Yii::$app->request->post())) {
      $transaction = Product::getDb()->beginTransaction();
      try {
        $modelVariation->imageFile = UploadedFile::getInstance($modelVariation, 'imageFile');
        if ($modelVariation->imageFile) {
          if ($path = $modelVariation->uploadImage()) {
            $modelVariation->image_url = $path;
          } else {
            throw new Exception('Failed to upload variation image to S3. Please check S3 credentials and bucket permissions.');
          }
        }
        $modelVariation->imageFile = null;

        if (!$modelVariation->save(false)) {
          throw new Exception(
            'Failed to Update Variation! Code: ' . json_encode($modelVariation->getFirstErrors()),
          );
        }

        [$typeIds, $values] = $this->getPostedDescriptions();
        $this->saveVariationDescriptions($modelVariation->id, $typeIds, $values);

        $transaction->commit();
        Yii::$app->session->setFlash('success', 'Variation updated successfully.');
        return $this->redirect(['view', 'id' => $productId]);
      } catch (Exception $ex) {
        $transaction->rollBack();
        Yii::$app->session->setFlash('warning', $ex->getMessage());
        return $this->redirect(['view', 'id' => $productId]);
      }
    }

    return $this->renderAjax('_variation_modal_form', [
      'modelVariation' => $modelVariation,
      'sources' => $this->getSources(),
      'warranties' => $this->getWarranties(),
      'submitLabel' => 'Save Changes',
    ]);
  }

  public function actionDuplicateVariation($id)
  {
    $sourceVariation = ProductVariation::findOne($id);
    if (!$sourceVariation) {
      throw new NotFoundHttpException('The requested variation does not exist.');
    }

    $modelVariation = new ProductVariation();
    $modelVariation->attributes = $sourceVariation->attributes;
    $modelVariation->id = null;
    $modelVariation->isNewRecord = true;
    $modelVariation->name = trim((string) $sourceVariation->name);
    $modelVariation->status = ProductVariation::STATUS_ACTIVE;

    if ($modelVariation->load(Yii::$app->request->post())) {
      $transaction = Product::getDb()->beginTransaction();
      try {
        $modelVariation->product_id = (int) $sourceVariation->product_id;
        $modelVariation->imageFile = UploadedFile::getInstance($modelVariation, 'imageFile');
        if ($modelVariation->imageFile) {
          if ($path = $modelVariation->uploadImage()) {
            $modelVariation->image_url = $path;
          } else {
            throw new Exception('Failed to upload variation image to S3. Please check S3 credentials and bucket permissions.');
          }
        }
        $modelVariation->imageFile = null;

        if (!$modelVariation->save(false)) {
          throw new Exception(
            'Failed to Duplicate Variation! Code: ' . json_encode($modelVariation->getFirstErrors()),
          );
        }

        [$typeIds, $values] = $this->getPostedDescriptions();
        $this->saveVariationDescriptions($modelVariation->id, $typeIds, $values);

        $transaction->commit();
        Yii::$app->session->setFlash('success', 'Variation duplicated successfully.');
        return $this->redirect(['view', 'id' => $sourceVariation->product_id]);
      } catch (Exception $ex) {
        $transaction->rollBack();
        Yii::$app->session->setFlash('warning', $ex->getMessage());
        return $this->redirect(['view', 'id' => $sourceVariation->product_id]);
      }
    }

    return $this->renderAjax('_variation_modal_form', [
      'modelVariation' => $modelVariation,
      'sources' => $this->getSources(),
      'warranties' => $this->getWarranties(),
      'submitLabel' => 'Save as New Variation',
      'descriptionSourceVariationId' => (int) $sourceVariation->id,
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
        if (!$model->save(false)) {
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
      'conditions' => $this->getConditions(),
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

    $variation->status = ProductVariation::STATUS_DELETED;
    $variation->save(false);

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

  /**
   * Search products by SKU code for select2
   */
  public function actionSearchBySku()
  {
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    $q = Yii::$app->request->get('q', '');
    $results = [];

    if (strlen($q) >= 2) {
      $variations = ProductVariation::find()
        ->select(['product_variation.id', 'product_variation.product_id', 'product.sku', 'product_variation.name', 'product.name as product_name'])
        ->innerJoin('product', 'product.id = product_variation.product_id')
        ->where(['or', 
          ['like', 'product.sku', $q],
          ['like', 'product.name', $q],
          ['like', 'product_variation.name', $q],
        ])
        ->andWhere(['product_variation.status' => [null, 1]])
        ->limit(20)
        ->all();

      foreach ($variations as $variation) {
        $displayText = $variation->sku ? $variation->sku . ' - ' . $variation->product_name : $variation->product_name;
        if ($variation->name) {
          $displayText .= ' (' . $variation->name . ')';
        }
        $results[] = [
          'id' => $variation->product_id,
          'text' => $displayText,
        ];
      }
    }

    return ['results' => $results];
  }
}

