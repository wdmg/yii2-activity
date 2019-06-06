<?php

namespace wdmg\activity\controllers;

use Yii;
use wdmg\activity\models\Activity;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * ActivityController implements the CRUD actions for Settings model.
 */
class ActivityController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['get'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'roles' => ['admin'],
                        'allow' => true
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all of user activity.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new Activity();
        $activity = new ActiveDataProvider([
            'query' => $model::find(),
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
        return $this->render('index', [
            'activity' => $activity,
            'model' => $model
        ]);
    }

    /**
     * Finds the Option model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Settings the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Activity::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app/modules/activity', 'The requested page does not exist.'));
    }
}
