<?php

namespace app\controllers;

use app\models\UserRole;
use app\models\UserRoleSearch;
use app\models\UserRoleAction;
use app\models\UserRoleGroup;
use app\models\UserRolePermission;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Exception;

/**
 * UserPermissionController implements the CRUD actions for UserRole model.
 */
class UserPermissionController extends Controller
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
   * Lists all UserRole models.
   *
   * @return string
   */
  public function actionIndex()
  {
    $searchModel = new UserRoleSearch();
    $dataProvider = $searchModel->search($this->request->queryParams);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
    ]);
  }

  /**
   * Creates a new UserRole model.
   *
   * @return string|\yii\web\Response
   */
  public function actionCreate()
  {
    $model = new UserRole();

    if ($this->request->isPost && $model->load($this->request->post())) {
      $transaction_exception = Yii::$app->db->beginTransaction();

      try {
        if (!$model->save()) {
          throw new Exception('Failed to Save! Code #001');
        }

        $chkboxAction = $this->request->post('chkboxAction');
        if (!empty($chkboxAction)) {
          $bulkData = [];
          foreach ($chkboxAction as $key => $value) {
            if (!empty($value)) {
              $bulkData[] = [$model->id, $value];
            }
          }
          $batchInsert = Yii::$app->db
            ->createCommand()
            ->batchInsert(
              'user_role_permission',
              ['user_role_id', 'action_id'],
              $bulkData,
            );
          if (!$batchInsert->execute()) {
            throw new Exception('Failed to save role item!');
          }
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
        Yii::$app->session->setFlash('success', 'Role saved successfully');
        return $this->redirect(['update', 'id' => $model->id]);
      } catch (Exception $ex) {
        Yii::$app->session->setFlash('warning', $ex->getMessage());
        $transaction_exception->rollBack();
        return $this->redirect(Yii::$app->request->referrer);
      }
    }
    $user_role_id = null;
    $userRoleAction = Yii::$app->db
      ->createCommand(
        "SELECT 
        user_role_group.`name` group_name,
        user_role_action.*,
        IF(user_role_permission.id IS NULL OR user_role_permission.id = '', 0,1) checked
      FROM user_role_action
      INNER JOIN user_role_group ON user_role_group.id = user_role_action.group_id
      LEFT JOIN user_role_permission ON user_role_permission.action_id = user_role_action.id 
        AND user_role_permission.user_role_id = :user_role_id
    ",
      )
      ->bindParam(':user_role_id', $user_role_id)
      ->queryAll();
    $userRoleActionByGroup = [];
    if (!empty($userRoleAction)) {
      foreach ($userRoleAction as $key => $value) {
        $userRoleActionByGroup[$value['group_name']][] = $value;
      }
    }

    return $this->renderAjax('_form', [
      'model' => $model,
      'userRoleAction' => $userRoleAction,
      'userRoleActionByGroup' => $userRoleActionByGroup,
    ]);
  }

  /**
   * Updates an existing UserRole model.
   *
   * @param int $id ID
   * @return string|\yii\web\Response
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionUpdate($id)
  {
    $model = $this->findModel($id);

    if ($this->request->isPost && $model->load($this->request->post())) {
      $transaction_exception = Yii::$app->db->beginTransaction();

      try {
        $chkboxAction = $this->request->post('chkboxAction');
        UserRolePermission::deleteAll(['user_role_id' => $model->id]);
        if (!empty($chkboxAction)) {
          $bulkData = [];
          foreach ($chkboxAction as $key => $value) {
            if (!empty($value)) {
              $bulkData[] = [$model->id, $value];
            }
          }
          $batchInsert = Yii::$app->db
            ->createCommand()
            ->batchInsert(
              'user_role_permission',
              ['user_role_id', 'action_id'],
              $bulkData,
            );
          if (!$batchInsert->execute()) {
            throw new Exception('Failed to save item!');
          }
        }

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
        Yii::$app->session->setFlash('success', 'Role saved successfully');
        return $this->redirect(Yii::$app->request->referrer);
      } catch (Exception $ex) {
        Yii::$app->session->setFlash('warning', $ex->getMessage());
        $transaction_exception->rollBack();
        return $this->redirect(Yii::$app->request->referrer);
      }
    }
    $user_role_id = $model->id;

    $userRoleAction = Yii::$app->db
      ->createCommand(
        "SELECT 
        user_role_group.`name` group_name,
        user_role_action.*,
        IF(user_role_permission.id IS NULL OR user_role_permission.id = '', 0,1) checked
      FROM user_role_action
      INNER JOIN user_role_group ON user_role_group.id = user_role_action.group_id
      LEFT JOIN user_role_permission ON user_role_permission.action_id = user_role_action.id 
        AND user_role_permission.user_role_id = :user_role_id
    ",
      )
      ->bindParam(':user_role_id', $user_role_id)
      ->queryAll();
    $userRoleActionByGroup = [];
    if (!empty($userRoleAction)) {
      foreach ($userRoleAction as $key => $value) {
        $userRoleActionByGroup[$value['group_name']][] = $value;
      }
    }

    return $this->renderAjax('_form', [
      'model' => $model,
      'userRoleAction' => $userRoleAction,
      'userRoleActionByGroup' => $userRoleActionByGroup,
    ]);
  }

  /**
   * Deletes an existing UserRole model.
   *
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
        'message' => 'Role deleted successfully.',
      ]);
    }

    Yii::$app->session->setFlash('success', 'Role Deleted Successfully');
    return $this->redirect(['index']);
  }

  /**
   * Finds the UserRole model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param int $id ID
   * @return UserRole the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = UserRole::findOne(['id' => $id])) !== null) {
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }
}
