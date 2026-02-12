<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\UserRole;

/**
 * UserRoleSearch represents the model behind the search form of `app\models\UserRole`.
 */
class UserRoleSearch extends UserRole
{
  public $globalSearch;

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [[['id'], 'integer'], [['name', 'globalSearch'], 'safe']];
  }

  /**
   * {@inheritdoc}
   */
  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return Model::scenarios();
  }

  /**
   * Creates data provider instance with search query applied
   *
   * @param array $params
   *
   * @return ActiveDataProvider
   */
  public function search($params)
  {
    $query = UserRole::find();

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
    ]);

    $this->load($params);

    if (!$this->validate()) {
      return $dataProvider;
    }

    $query->andFilterWhere([
      'id' => $this->id,
    ]);

    $query->andFilterWhere(['like', 'name', $this->name]);

    $query->andFilterWhere(['or', ['like', 'name', $this->globalSearch]]);

    return $dataProvider;
  }
}
