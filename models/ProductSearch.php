<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Product;

/**
 * ProductSearch represents the model behind the search form of `app\models\Product`.
 */
class ProductSearch extends Product
{
  public $globalSearch;

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [
        ['id', 'brand_id', 'category_id', 'status', 'created_by', 'updated_by'],
        'integer',
      ],
      [
        [
          'image',
          'name',
          'sku',
          'serial',
          'description',
          'created_at',
          'updated_at',
          'globalSearch',
        ],
        'safe',
      ],
      [['cost', 'price'], 'number'],
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
    $query = Product::find();

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
      'brand_id' => $this->brand_id,
      'category_id' => $this->category_id,
      'cost' => $this->cost,
      'price' => $this->price,
      'status' => $this->status,
      'created_at' => $this->created_at,
      'created_by' => $this->created_by,
      'updated_at' => $this->updated_at,
      'updated_by' => $this->updated_by,
    ]);

    $query->andFilterWhere([
      'or',
      ['like', 'name', $this->globalSearch],
      ['like', 'sku', $this->globalSearch],
      ['like', 'serial', $this->globalSearch],
      ['like', 'description', $this->globalSearch],
    ]);

    $query
      ->andFilterWhere(['like', 'image', $this->image])
      ->andFilterWhere(['like', 'name', $this->name])
      ->andFilterWhere(['like', 'sku', $this->sku])
      ->andFilterWhere(['like', 'serial', $this->serial])
      ->andFilterWhere(['like', 'description', $this->description]);

    return $dataProvider;
  }
}
