<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PurchaseOrder;

/**
 * PurchaseOrderSearch represents the model behind the search form of `app\models\PurchaseOrder`.
 */
class PurchaseOrderSearch extends PurchaseOrder
{
  public $globalSearch;
  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [['id', 'supplier_id', 'status', 'created_by', 'updated_by'], 'integer'],
      [
        [
          'code',
          'serial_code',
          'date',
          'due_date',
          'remark',
          'created_at',
          'updated_at',
          'globalSearch',
        ],
        'safe',
      ],
      [
        [
          'delivery_fee',
          'extra_charge',
          'discount_amount',
          'cost_total',
          'sub_total',
          'grand_total',
          'paid_amount',
          'balance_amount',
        ],
        'number',
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
    $query = PurchaseOrder::find();
    $query->andWhere(['!=', 'status', PurchaseOrder::STATUS_DELETED]);

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
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
      'supplier_id' => $this->supplier_id,
      'date' => $this->date,
      'due_date' => $this->due_date,
      'delivery_fee' => $this->delivery_fee,
      'extra_charge' => $this->extra_charge,
      'discount_amount' => $this->discount_amount,
      'cost_total' => $this->cost_total,
      'sub_total' => $this->sub_total,
      'grand_total' => $this->grand_total,
      'paid_amount' => $this->paid_amount,
      'balance_amount' => $this->balance_amount,
      'status' => $this->status,
      'created_at' => $this->created_at,
      'created_by' => $this->created_by,
      'updated_at' => $this->updated_at,
      'updated_by' => $this->updated_by,
    ]);

    $query
      ->andFilterWhere(['like', 'code', $this->code])
      ->andFilterWhere(['like', 'serial_code', $this->serial_code])
      ->andFilterWhere(['like', 'remark', $this->remark]);

    $query->andFilterWhere([
      'or',
      ['like', 'code', $this->globalSearch],
      ['like', 'serial_code', $this->globalSearch],
    ]);

    return $dataProvider;
  }
}
