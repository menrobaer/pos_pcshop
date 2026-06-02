<?php

namespace app\models\website;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ProductModelSearch represents the model behind the search form of `app\models\ProductModel`.
 */
class ProductModelSearch extends ProductModel
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // Strict integer filter for the primary key
            [['id','status','brand_id','sort'], 'integer'],
            
            // Textual filters for fuzzy matching
            [['name', 'image_url'], 'safe'],
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
        $query = ProductModel::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'sort' => SORT_ASC,
                    'id' => SORT_ASC,
                ]
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // Grid filtering conditions: Strict matches
        $query->andFilterWhere([
            'id' => $this->id,
            'brand_id' => $this->brand_id,
        ]);

        // Fuzzy filtering conditions: Partial matches
        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->andFilterWhere(['status' => $this->status]);

        return $dataProvider;
    }
}