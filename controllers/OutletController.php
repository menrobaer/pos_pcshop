<?php

namespace app\controllers;

use Yii;
use app\models\Outlet;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * OutletController implements the CRUD actions for Outlet model.
 */
class OutletController extends Controller
{
  /**
   * @inheritDoc
   */
  public function behaviors()
  {
    return array_merge(parent::behaviors(), [
      'verbs' => [
        'class' => VerbFilter::className(),
        'actions' => [
          'delete' => ['POST'],
        ],
      ],
    ]);
  }

  /**
   * Redirects to the update page for the first outlet.
   * If no outlet exists, it creates one with default values and redirects.
   */
  public function actionIndex()
  {
    $model = Outlet::find()->one();

    if ($model === null) {
      $model = new Outlet();
      $model->name = 'Main Store';
      $model->status = 1;
      if (!$model->save()) {
        Yii::$app->session->setFlash(
          'error',
          'Could not initialize outlet settings.',
        );
        return $this->redirect(['/site/index']);
      }
    }

    return $this->redirect(['update', 'id' => $model->id]);
  }

  /**
   * Updates an existing Outlet model.
   * If update is successful, the browser will be redirected to the 'update' page.
   * @param int $id ID
   * @return string|\yii\web\Response
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionUpdate($id)
  {
    $model = $this->findModel($id);

    if ($this->request->isPost && $model->load($this->request->post())) {
      $transaction = Yii::$app->db->beginTransaction();
      try {
        $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
        if ($model->imageFile) {
          $oldImage = $model->image;
          if ($path = $model->uploadImage()) {
            $model->image = $path;
            if ($oldImage && file_exists($oldImage)) {
              unlink($oldImage);
            }
          }
        }

        $model->imageFile = null;
        if (!$model->save()) {
          throw new \Exception(
            'Failed to update: ' . json_encode($model->getErrors()),
          );
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
          'Outlet settings updated successfully.',
        );
        return $this->refresh();
      } catch (\Exception $e) {
        $transaction->rollBack();
        Yii::$app->session->setFlash('error', $e->getMessage());
      }
    }

    return $this->render('update', [
      'model' => $model,
    ]);
  }

  /**
   * Finds the Outlet model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param int $id ID
   * @return Outlet the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = Outlet::findOne(['id' => $id])) !== null) {
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }
}
