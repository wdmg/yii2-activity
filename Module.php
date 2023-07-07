<?php

namespace wdmg\activity;

/**
 * Yii2 Activity
 *
 * @category        Module
 * @version         1.2.2
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-activity
 * @copyright       Copyright (c) 2019 - 2023 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

use wdmg\helpers\ArrayHelper;
use Yii;
use wdmg\base\BaseModule;
use yii\base\Controller;

/**
 * Activity module definition class
 */
class Module extends BaseModule
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'wdmg\activity\controllers';

    /**
     * {@inheritdoc}
     */
    public $defaultRoute = 'list/index';

    /**
     * @var string, the name of module
     */
    public $name = "Activity";

    /**
     * @var string, the description of module
     */
    public $description = "User activity tracking system";

    /**
     * @var string the module version
     */
    private $version = "1.2.2";

    /**
     * @var integer, priority of initialization
     */
    private $priority = 5;

    /**
     * Autoupdate list
     *
     * @var bool
     */
    public $autoupdateList = true;

    /**
     * Log of web-surfing activity
     *
     * @var bool
     * @see $backendSurfing, $frontendSurfing
     */
    public $surfingActivity = false;

    /**
     * Log of web-surfing activity by backend.
     * It`s work only for authorized users.
     *
     * @var bool
     */
    public $backendSurfing = true;

    /**
     * Log of web-surfing activity by frontend.
     * It`s work only for authorized users.
     *
     * @var bool
     */
    public $frontendSurfing = false;

    /**
     * Ignoring activity by request route
     *
     * @var array
     */
    public $ignoringRoutes = [
        // like '/admin'
    ];

    /**
     * Ignoring activity by user ID
     *
     * @var array
     */
    public $ignoringUsers = [
        // like '100'
    ];

    /**
     * Ignoring activity by user IP
     *
     * @var array
     */
    public $ignoringIp = [
        // like '127.0.0.1'
    ];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Set version of current module
        $this->setVersion($this->version);

        // Set priority of current module
        $this->setPriority($this->priority);

        if (isset(Yii::$app->params['activity.autoupdateList']))
            $this->autoupdateList = Yii::$app->params['activity.autoupdateList'];

        if (isset(Yii::$app->params['activity.surfingActivity']))
            $this->surfingActivity = Yii::$app->params['activity.surfingActivity'];

        if (isset(Yii::$app->params['activity.backendSurfing']))
            $this->backendSurfing = Yii::$app->params['activity.backendSurfing'];

        if (isset(Yii::$app->params['activity.frontendSurfing']))
            $this->frontendSurfing = Yii::$app->params['activity.frontendSurfing'];

        if (isset(Yii::$app->params['activity.ignoringRoutes']))
            $this->ignoringRoutes = Yii::$app->params['activity.ignoringRoutes'];

        if (isset(Yii::$app->params['activity.ignoringUsers']))
            $this->ignoringUsers = Yii::$app->params['activity.ignoringUsers'];

        if (isset(Yii::$app->params['activity.ignoringIp']))
            $this->ignoringIp = Yii::$app->params['activity.ignoringIp'];

    }

    /**
     * {@inheritdoc}
     */
    public function dashboardNavItems($options = null)
    {
        $items = [
            'label' => $this->name,
            'icon' => 'fa fa-fw fa-chart-bar',
            'url' => [$this->routePrefix . '/'. $this->id . '/' . $this->defaultRoute],
            'active' => (in_array(\Yii::$app->controller->module->id, [$this->id]) &&  Yii::$app->controller->id == 'list'),
        ];

	    if (!is_null($options)) {

		    if (isset($options['count'])) {
			    $items['label'] .= '<span class="badge badge-default float-right">' . $options['count'] . '</span>';
			    unset($options['count']);
		    }

		    if (is_array($options))
			    $items = ArrayHelper::merge($items, $options);

	    }

	    return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrap($app)
    {
        parent::bootstrap($app);

        // Configure activity component
        $app->setComponents([
            'activity' => [
                'class' => 'wdmg\activity\components\Activity'
            ]
        ]);

        // Register of web-surfing activity
        if (
            !($app instanceof \yii\console\Application) &&
            $this->module &&
            $this->surfingActivity
        ) {
            if (
                !(Yii::$app->user->isGuest) &&
                (
                    ($this->isBackend() && $this->backendSurfing) ||
                    (!$this->isBackend() && $this->frontendSurfing)
                )
            ) {
                \yii\base\Event::on(\yii\base\Controller::class, \yii\base\Controller::EVENT_BEFORE_ACTION, function ($event) {
                    if (!Yii::$app->request->isAjax) {
                        $status = 'info';
                        if (Yii::$app->response->getStatusCode() >= 400 && Yii::$app->response->getStatusCode() < 500)
                            $status = 'warning';
                        else if (Yii::$app->response->getStatusCode() >= 500)
                            $status = 'danger';

                        Yii::$app->activity->set('User has request URI: ' . Yii::$app->request->getUrl(), $event->name, $status, 2);
                    }
                });
            }
        }
    }
}