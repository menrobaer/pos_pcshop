<?php

namespace app\models\website;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\website\Product;

/**
 * ProductSearch represents the model behind the search form of `app\models\website\Product`.
 */
class ProductSearch extends Product
{
    /**
     * {@inheritdoc}
     */
    public $globalSearch; // For a general search across multiple fields
    public function rules()
    {
        return [
            // Safe rules for integer/numeric lookups
            [['id', 'category_id', 'brand_id', 'model_id', 'condition_id', 'status', 'created_by', 'updated_by', 'available'], 'integer'],
            
            // Safe rules for numeric/decimal ranges
            [['price', 'cost', 'markup'], 'number'],
            
            // Safe rules for text matching and date filters
            [['source', 'sku', 'code', 'slug', 'name', 'full_price', 'image_url', 'warranty', 'description', 'free', 'stock', 'created_at', 'updated_at'], 'safe'],
            [['globalSearch'], 'safe'], // Global search field
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
        $query->where(['!=', 'status', self::STATUS_DELETED]); // Exclude deleted items by default

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC, // Newest items first by default
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

        // Grid filtering conditions: Strict matches for IDs, status, and metrics
        $query->andFilterWhere([
            'id' => $this->id,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'model_id' => $this->model_id,
            'condition_id' => $this->condition_id,
            'price' => $this->price,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
            'cost' => $this->cost,
            'markup' => $this->markup,
            'available' => $this->available,
        ]);

        // Global matching: one keyword can match any searchable text field.
        $globalSearch = trim((string) $this->globalSearch);
        if ($globalSearch !== '') {
            $query->andWhere([
                'or',
                ['like', 'sku', $globalSearch],
                ['like', 'code', $globalSearch],
                ['like', 'slug', $globalSearch],
                ['like', 'name', $globalSearch],
                ['like', 'warranty', $globalSearch],
                ['like', 'description', $globalSearch],
                ['like', 'free', $globalSearch],
                ['like', 'stock', $globalSearch],
            ]);
        }

        return $dataProvider;
    }
}