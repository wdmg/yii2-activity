<?php

namespace wdmg\activity;

/**
 * Yii2 Activity
 *
 * @category        Module
 * @version         1.1.6
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-activity
 * @copyright       Copyright (c) 2019 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

use Yii;
use wdmg\base\BaseModule;

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
    private $version = "1.1.6";

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
    public function bootstrap($app)
    {
        parent::bootstrap($app);

        // Configure activity component
        $app->setComponents([
            'activity' => [
                'class' => 'wdmg\activity\components\Activity'
            ]
        ]);
    }
}