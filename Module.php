<?php

namespace wdmg\activity;

/**
 * Yii2 Activity
 *
 * @category        Module
 * @version         1.1.9
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-activity
 * @copyright       Copyright (c) 2019 - 2020 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

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
    public $defaultRoute = 'activity/index';

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
    private $version = "1.1.9";

    /**
     * @var integer, priority of initialization
     */
    private $priority = 5;

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

    }

    /**
     * {@inheritdoc}
     */
    public function dashboardNavItems($createLink = false)
    {
        $items = [
            'label' => $this->name,
            'url' => [$this->routePrefix . '/'. $this->id],
            'icon' => 'fa fa-fw fa-chart-bar',
            'active' => in_array(\Yii::$app->controller->module->id, [$this->id])
        ];
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

        if(!($app instanceof \yii\console\Application) && $this->module) {

            // Register simply activity
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