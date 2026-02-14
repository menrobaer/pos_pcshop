<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\ProductBrand;

/**
 * ProductBrandSearch represents the model behind the search form of `app\models\ProductBrand`.
 */
class ProductBrandSearch extends ProductBrand
{
  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [['id', 'status', 'created_by', 'updated_by'], 'integer'],
      [['name', 'created_at', 'updated_at'], 'safe'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function scenarios()
  {
    return Model::scenarios();
  }

  /**
   * Creates data provider instance with search query applied
   */
  public function search($params)
  {
    $query = ProductBrand::find();
    $query->andWhere(['!=', 'status', ProductBrand::STATUS_DELETED]);

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
    ]);

    $this->load($params);

    if (!$this->validate()) {
      return $dataProvider;
    }

    $query->andFilterWhere([
      'id' => $this->id,
      'status' => $this->status,
      'created_at' => $this->created_at,
      'created_by' => $this->created_by,
      'updated_at' => $this->updated_at,
      'updated_by' => $this->updated_by,
    ]);

    $query->andFilterWhere(['like', 'name', $this->name]);

    return $dataProvider;
  }
}
