<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\ActivityLog;

/**
 * ActivityLogSearch represents the model behind the search form of `app\models\ActivityLog`.
 */
class ActivityLogSearch extends ActivityLog
{
  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [['id', 'user_id'], 'integer'],
      [
        [
          'controller',
          'action',
          'method',
          'params',
          'ip_address',
          'user_agent',
          'created_at',
        ],
        'safe',
      ],
    ];
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
    $query = ActivityLog::find();

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
    ]);

    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      // $query->where('0=1');
      return $dataProvider;
    }

    // grid filtering conditions
    $query->andFilterWhere([
      'id' => $this->id,
      'user_id' => $this->user_id,
      'created_at' => $this->created_at,
    ]);

    $query
      ->andFilterWhere(['like', 'controller', $this->controller])
      ->andFilterWhere(['like', 'action', $this->action])
      ->andFilterWhere(['like', 'method', $this->method])
      ->andFilterWhere(['like', 'params', $this->params])
      ->andFilterWhere(['like', 'ip_address', $this->ip_address])
      ->andFilterWhere(['like', 'user_agent', $this->user_agent]);

    return $dataProvider;
  }
}
