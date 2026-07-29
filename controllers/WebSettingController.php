<?php

namespace app\controllers;

use app\models\website\Setting;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * WebSettingController implements the CRUD actions for Setting model.
 */
class WebSettingController extends Controller
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
    return Yii::getAlias('@app/views/w-setting');
  }

  /**
   * Shows and updates the primary site setting record.
   */
  public function actionIndex()
  {
    $model = Setting::findOne(1);
    if ($model === null) {
      throw new NotFoundHttpException('The requested page does not exist.');
    }

    if (Yii::$app->request->isPost) {
      $post = Yii::$app->request->post('Setting', []);
      $model->value = $post['value'] ?? $model->value;

      if ($model->save()) {
        Yii::$app->session->setFlash('success', 'Setting updated successfully.');
        return $this->refresh();
      }

      Yii::$app->session->setFlash('error', 'Unable to update setting.');
    }

    return $this->render('index', [
      'model' => $model,
    ]);
  }

}
