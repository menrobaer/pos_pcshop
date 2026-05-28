<?php

namespace app\models\website;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Search represents the model behind the search form of `app\models\ProductDescriptionType`.
 */
class ProductDescriptionTypeSearch extends ProductDescriptionType
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // Strict integer filters
            [['id', 'status', 'sort'], 'integer'],
            
            // Textual filters for fuzzy matching
            [['name'], 'safe'],
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
        $query = ProductDescriptionType::find();
        $query->andWhere(['!=', 'status', ProductDescriptionType::STATUS_DELETED]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'sort' => SORT_ASC, // Sort by display sequence by default
                    'id' => SORT_DESC,
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
            'status' => $this->status,
            'sort' => $this->sort,
        ]);

        // Fuzzy filtering conditions: Partial matches
        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}