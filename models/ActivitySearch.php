<?php

namespace wdmg\activity\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use wdmg\activity\models\Activity;

/**
 * ActivitySearch represents the model behind the search form of `wdmg\activity\models\Activity`.
 */
class ActivitySearch extends Activity
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['message', 'created_at', 'action', 'type', 'metadata'], 'safe'],
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
        $query = Activity::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
	        'pagination' => [
		        'pageSize' => 20,
	        ],
	        'sort' => [
		        'defaultOrder' => [
			        'id' => SORT_DESC,
			        'created_at' => SORT_ASC,
		        ]
	        ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id
        ]);

        if ($this->action !== "*")
            $query->andFilterWhere(['like', 'action', $this->action]);

        if ($this->message !== "*")
            $query->andFilterWhere(['like', 'message', $this->message]);

        if ($this->type !== "*")
            $query->andFilterWhere(['like', 'type', $this->type]);

        if ($this->created_by !== "*")
            $query->andFilterWhere(['like', 'created_by', $this->created_by]);

        if (!empty($this->created_at))
	        $query->andFilterWhere(['<','created_at', strtotime($this->created_at)]);

        return $dataProvider;
    }

}
