<?php

namespace wdmg\activity\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use wdmg\activity\models\Activity;
use wdmg\activity\models\ActivitySearch;

/**
 * ListController implements the CRUD actions for Activity model.
 */
class ListController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['get'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'roles' => ['admin'],
                        'allow' => true
                    ],
                ],
            ],
        ];

        // If auth manager not configured use default access control
        if(!Yii::$app->authManager) {
            $behaviors['access'] = [
                'class' => AccessControl::class,
                'except' => ['login'],
                'rules' => [
                    [
                        'roles' => ['@'],
                        'allow' => true
                    ]
                ],
            ];
        }

        return $behaviors;
    }

    /**
     * Lists all of user activity.
     * @return mixed
     */
    public function actionIndex()
    {

		// Set autoupdate list flag by default
	    $this->view->params['auto-update'] = (bool)$this->module->autoupdateList;

	    // Set autoupdate list flag by user options
		if (!Yii::$app->user->isGuest && class_exists('\wdmg\users\models\Users')) {
		    $user_options = \wdmg\users\models\Users::getOptions('activity', []);

		    if (isset($user_options['autoupdate']))
			    $this->view->params['auto-update'] = (bool)$user_options['autoupdate'];

	    }

	    $searchModel = new ActivitySearch();
	    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

	    return $this->render('index', [
		    'searchModel' => $searchModel,
		    'dataProvider' => $dataProvider,
		    'module' => $this->module
	    ]);
    }

	/**
	 * Add/remove from favourites
	 *
	 * @return Response
	 */
	public function actionAutoupdate()
	{

		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

		if (!Yii::$app->user->isGuest && class_exists('\wdmg\users\models\Users')) {

			$autoupdate = \Yii::$app->request->post('autoupdate', null);
			if (isset($autoupdate)) {

				$user_options = \wdmg\users\models\Users::getOptions('activity', []);
				if (!is_array($user_options))
					$user_options = [];

				$is_autoupdate = !($autoupdate == "false");
				$user_options = array_merge($user_options, [
					'autoupdate' => $is_autoupdate
				]);

				\wdmg\users\models\Users::setOptions(['activity' => [
					'autoupdate' => $is_autoupdate
				]], false);

				return ['success' => true, 'autoupdate' => $is_autoupdate];
			}
		}

		return ['success' => false];
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
