<?php

namespace app\controllers;

use app\models\User;
use app\models\UserRole;
use app\models\UserRolePermission;
use app\models\UserSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
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
   * Lists all User models.
   *
   * @return string
   */
  public function actionIndex()
  {
    $searchModel = new UserSearch();
    if (empty($this->request->queryParams)) {
      $searchModel->status = User::STATUS_ACTIVE;
    }
    $dataProvider = $searchModel->search($this->request->queryParams);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
    ]);
  }

  /**
   * Creates a new User model.
   * If creation is successful, the browser will be redirected to the 'index' page.
   * @return string|\yii\web\Response
   */
  public function actionCreate()
  {
    $model = new User();

    if ($model->load(Yii::$app->request->post())) {
      $model->scenario = 'create';
      if ($model->new_password) {
        $model->password_hash = Yii::$app->security->generatePasswordHash(
          $model->new_password,
        );
      }
      $model->auth_key = Yii::$app->security->generateRandomString();
      $model->created_at = time();
      $model->updated_at = time();

      $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
      if ($model->imageFile) {
        if ($path = $model->uploadImage()) {
          $model->image = $path;
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
        Yii::$app->session->setFlash('success', 'User Saved Successfully');
        return $this->redirect(Yii::$app->request->referrer);
      }
    }

    return $this->renderAjax('_form', [
      'model' => $model,
      'roles' => $this->getRoles(),
    ]);
  }

  protected function getRoles()
  {
    return \yii\helpers\ArrayHelper::map(UserRole::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');
  }

  /**
   * Updates an existing User model.
   * If update is successful, the browser will be redirected to the 'index' page.
   * @param int $id ID
   * @return string|\yii\web\Response
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionUpdate($id)
  {
    $model = $this->findModel($id);
    $old_password = $model->password_hash;

    if ($model->load(Yii::$app->request->post())) {
      if ($model->new_password) {
        $model->password_hash = Yii::$app->security->generatePasswordHash(
          $model->new_password,
        );
      }
      $model->updated_at = time();

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
        Yii::$app->session->setFlash('success', 'User Updated Successfully');
        return $this->redirect(Yii::$app->request->referrer);
      }
    }

    return $this->renderAjax('_form', [
      'model' => $model,
      'roles' => $this->getRoles(),
    ]);
  }

  /**
   * Deletes an existing User model.
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
    $model->delete();

    if (Yii::$app->request->isAjax) {
      return $this->asJson([
        'success' => true,
        'message' => 'User deleted successfully.',
      ]);
    }

    Yii::$app->session->setFlash('success', 'User Deleted Successfully');
    return $this->redirect(['index']);
  }

  public function actionProfile()
  {
    $model = $this->findModel(Yii::$app->user->id);
    $originalHash = $model->password_hash;

    if ($model->load(Yii::$app->request->post())) {
      if ($model->new_password) {
        $model->password_hash = Yii::$app->security->generatePasswordHash(
          $model->new_password,
        );
      } else {
        $model->password_hash = $originalHash;
      }
      $model->updated_at = time();

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

      if ($model->save()) {
        Yii::$app->session->setFlash(
          'success',
          'Profile updated successfully.',
        );
        return $this->refresh();
      }
      Yii::$app->session->setFlash('error', 'Unable to save profile.');
    }

    return $this->render('profile', [
      'model' => $model,
    ]);
  }

  /**
   * Finds the User model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param int $id ID
   * @return User the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = User::findOne(['id' => $id])) !== null) {
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }
}
