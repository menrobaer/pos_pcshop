<?php

namespace app\controllers;

use app\models\website\Navigation;
use app\models\website\NavigationItem;
use app\models\website\NavigationItemData;
use Yii;
use yii\base\Exception;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

class WebNavigationController extends Controller
{
  protected function getWebsiteDb()
  {
    return Navigation::getDb();
  }

  public function behaviors()
  {
    return array_merge(
      parent::behaviors(),
      [
        // 'access' => [
        //   'class' => \yii\filters\AccessControl::class,
        //   'rules' => [
        //     [
        //       'actions' => \app\modules\admin\models\User::getUserPermission(Yii::$app->controller->id),
        //       'allow' => true,
        //     ]
        //   ],
        // ],
        'verbs' => [
          'class' => VerbFilter::class,
          'actions' => [
            'delete' => ['POST'],
            'delete-item' => ['POST'],
            'dependent' => ['POST'],
          ],
        ],
      ]
    );
  }

  public function getViewPath()
  {
    return Yii::getAlias('@app/views/w-navigation');
  }

  public function actions()
  {
    return [
      'error' => [
        'class' => 'yii\web\ErrorAction',
      ],
    ];
  }

  public function actionIndex()
  {
    $navigation = Navigation::find()->orderBy(['sort' => SORT_ASC])->all();
    return $this->render('index', [
      'navigation' => $navigation,
    ]);
  }

  public function actionCreate()
  {
    $model = new Navigation();
    if ($model->load(Yii::$app->request->post())) {

      $transaction_exception = $this->getWebsiteDb()->beginTransaction();

      try {
        if (!$model->save()) throw new Exception("Failed to Save! Code #001");

        $transaction_exception->commit();
        Yii::$app->session->setFlash('success', "Item created successfully");
        return $this->redirect(Yii::$app->request->referrer);
      } catch (Exception $ex) {
        Yii::$app->session->setFlash('warning', $ex->getMessage());
        $transaction_exception->rollBack();
        return $this->redirect(Yii::$app->request->referrer);
      }
    }
    return $this->renderAjax('_form', [
      'model' => $model
    ]);
  }

  public function actionUpdate($id)
  {
    $model = Navigation::findOne($id);
    if (!$model) {
      throw new NotFoundHttpException('The requested navigation does not exist.');
    }

    if ($model->load(Yii::$app->request->post())) {

      $transaction_exception = $this->getWebsiteDb()->beginTransaction();

      try {
        if (!$model->save()) throw new Exception("Failed to Save! Code #001");

        $transaction_exception->commit();
        Yii::$app->session->setFlash('success', "Item saved successfully");
        return $this->redirect(Yii::$app->request->referrer);
      } catch (Exception $ex) {
        Yii::$app->session->setFlash('warning', $ex->getMessage());
        $transaction_exception->rollBack();
        return $this->redirect(Yii::$app->request->referrer);
      }
    }
    return $this->renderAjax('_form', [
      'model' => $model
    ]);
  }

  public function actionDelete($id)
  {
    $model = Navigation::findOne($id);
    if (!$model) {
      throw new NotFoundHttpException('The requested navigation does not exist.');
    }

    $transaction_exception = $this->getWebsiteDb()->beginTransaction();

    try {
      $model->delete();

      $transaction_exception->commit();
      Yii::$app->session->setFlash('success', "Item deleted successfully");
      return $this->redirect(Yii::$app->request->referrer);
    } catch (Exception $ex) {
      Yii::$app->session->setFlash('warning', $ex->getMessage());
      $transaction_exception->rollBack();
      return $this->redirect(Yii::$app->request->referrer);
    }
  }

  public function actionAddItem($parent)
  {
    $navigation = Navigation::findOne($parent);
    if (!$navigation) {
      throw new NotFoundHttpException('The requested navigation does not exist.');
    }

    $model = new NavigationItem();
    $model->nav_id = $navigation->id;
    if ($model->load(Yii::$app->request->post())) {

      $transaction_exception = $this->getWebsiteDb()->beginTransaction();

      try {
        if (!$model->save()) throw new Exception("Failed to Save! Code #001");

        $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
        if ($model->imageFile) {
          if ($path = $model->uploadImage()) {
            $model->image_url = $path;
          }
          $model->imageFile = null;
          if (!$model->save(false, ['image_url'])) {
            throw new Exception("Failed to Save! Code #002");
          }
        }

        $categoryData = $model->category_id;
        if (!empty($categoryData)) {
          $batch_data = [];
          foreach ($categoryData as $key => $value) {
            $batch_data[] = [
              'id' => null,
              'nav_item_id' => $model->id,
              'category_id' => $value,
              'brand_id' => null,
            ];
          }
          $postModel = new NavigationItemData();
          if (!$this->getWebsiteDb()->createCommand()->batchInsert(NavigationItemData::tableName(), $postModel->attributes(), $batch_data)->execute()) {
            throw new Exception("Failed to save base data!");
          }
        }

        $brandData = $model->brand_id;
        if (!empty($brandData)) {
          $batch_data = [];
          foreach ($brandData as $key => $value) {
            $batch_data[] = [
              'id' => null,
              'nav_item_id' => $model->id,
              'category_id' => null,
              'brand_id' => $value,
            ];
          }
          $postModel = new NavigationItemData();
          if (!$this->getWebsiteDb()->createCommand()->batchInsert(NavigationItemData::tableName(), $postModel->attributes(), $batch_data)->execute()) {
            throw new Exception("Failed to save base data!");
          }
        }

        $transaction_exception->commit();
        Yii::$app->session->setFlash('success', "Item created successfully");
        return $this->redirect(Yii::$app->request->referrer);
      } catch (Exception $ex) {
        Yii::$app->session->setFlash('warning', $ex->getMessage());
        $transaction_exception->rollBack();
        return $this->redirect(Yii::$app->request->referrer);
      }
    }
    return $this->renderAjax('_form_item', [
      'model' => $model
    ]);
  }

  public function actionUpdateItem($id)
  {
    $model = NavigationItem::findOne($id);
    if (!$model) {
      throw new NotFoundHttpException('The requested navigation item does not exist.');
    }

    if ($model->load(Yii::$app->request->post())) {

      $transaction_exception = $this->getWebsiteDb()->beginTransaction();

      try {
        $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
        if ($model->imageFile) {
          if ($path = $model->uploadImage()) {
            $model->image_url = $path;
          }
        }
        $model->imageFile = null;
        if (!$model->save()) throw new Exception("Failed to Save! Code #001");
        NavigationItemData::deleteAll(['nav_item_id' => $model->id]);

        $categoryData = $model->category_id;
        if (!empty($categoryData)) {
          $batch_data = [];
          foreach ($categoryData as $key => $value) {
            $batch_data[] = [
              'id' => null,
              'nav_item_id' => $model->id,
              'category_id' => $value,
              'brand_id' => null,
            ];
          }
          $postModel = new NavigationItemData();
          if (!$this->getWebsiteDb()->createCommand()->batchInsert(NavigationItemData::tableName(), $postModel->attributes(), $batch_data)->execute()) {
            throw new Exception("Failed to save base data!");
          }
        }

        $brandData = $model->brand_id;
        if (!empty($brandData)) {
          $batch_data = [];
          foreach ($brandData as $key => $value) {
            $batch_data[] = [
              'id' => null,
              'nav_item_id' => $model->id,
              'category_id' => null,
              'brand_id' => $value,
            ];
          }
          $postModel = new NavigationItemData();
          if (!$this->getWebsiteDb()->createCommand()->batchInsert(NavigationItemData::tableName(), $postModel->attributes(), $batch_data)->execute()) {
            throw new Exception("Failed to save base data!");
          }
        }


        $transaction_exception->commit();
        Yii::$app->session->setFlash('success', "Item saved successfully");
        return $this->redirect(Yii::$app->request->referrer);
      } catch (Exception $ex) {
        Yii::$app->session->setFlash('warning', $ex->getMessage());
        $transaction_exception->rollBack();
        return $this->redirect(Yii::$app->request->referrer);
      }
    }
    return $this->renderAjax('_form_item', [
      'model' => $model
    ]);
  }

  public function actionDeleteItem($id)
  {
    $model = NavigationItem::findOne($id);
    if (!$model) {
      throw new NotFoundHttpException('The requested navigation item does not exist.');
    }

    $transaction_exception = $this->getWebsiteDb()->beginTransaction();

    try {
      /** @var \app\components\AwsSdk $awssdk */
      $awssdk = Yii::$app->awssdk;
      if (!empty($model->getImageKey())) {
        $awssdk->deleteByKey($model->getImageKey());
      }
      $model->delete();

      $transaction_exception->commit();
      Yii::$app->session->setFlash('success', "Item deleted successfully");
      return $this->redirect(Yii::$app->request->referrer);
    } catch (Exception $ex) {
      Yii::$app->session->setFlash('warning', $ex->getMessage());
      $transaction_exception->rollBack();
      return $this->redirect(Yii::$app->request->referrer);
    }
  }

  public function actionDependent()
  {
    if ($this->request->isAjax) {
      if ($this->request->post('action') === 'update_order') {
        $currentID = Yii::$app->request->post('currentID');
        $oldIndex = Yii::$app->request->post('oldIndex');
        $newIndex = Yii::$app->request->post('newIndex');
        $orderArr = Yii::$app->request->post('orderArr');

        $transaction_exception = $this->getWebsiteDb()->beginTransaction();
        try {
          if (!empty($orderArr)) {
            foreach ($orderArr as $key => $value) {
              $model = NavigationItem::findOne($value);
              if (!$model) {
                throw new Exception("failed to save -" . $value);
              }
              $model->sort = $key + 1;
              if (!$model->save()) throw new Exception("failed to save -" . $value);
            }
            $transaction_exception->commit();
            return json_encode('saved');
          }
        } catch (Exception $ex) {
          $transaction_exception->rollBack();
          return json_encode('failed');
        }
      }
      if ($this->request->post('action') === 'update_parent_order') {
        $currentID = Yii::$app->request->post('currentID');
        $oldIndex = Yii::$app->request->post('oldIndex');
        $newIndex = Yii::$app->request->post('newIndex');
        $orderArr = Yii::$app->request->post('orderArr');

        $transaction_exception = $this->getWebsiteDb()->beginTransaction();
        try {
          if (!empty($orderArr)) {
            foreach ($orderArr as $key => $value) {
              $model = Navigation::findOne($value);
              if (!$model) {
                throw new Exception("failed to save -" . $value);
              }
              $model->sort = $key + 1;
              if (!$model->save()) throw new Exception("failed to save -" . $value);
            }
            $transaction_exception->commit();
            return json_encode('saved');
          }
        } catch (Exception $ex) {
          $transaction_exception->rollBack();
          return json_encode('failed');
        }
      }
    }
  }

  public function actionValidation($id = null)
  {

    $model = $id === null ? new Navigation() : Navigation::findOne($id);
    if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
      Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      return \yii\widgets\ActiveForm::validate($model);
    }
  }
}
