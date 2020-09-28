<?php

namespace wdmg\activity\components;


/**
 * Yii2 Activity
 *
 * @category        Component
 * @version         1.1.12
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-activity
 * @copyright       Copyright (c) 2019 - 2020 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;

class Activity extends Component
{

    protected $module;
    protected $model;

    /**
     * Initialize the component
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (!$this->module = Yii::$app->getModule('activity'))
            $this->module = Yii::$app->getModule('admin/activity');

        $this->model = new \wdmg\activity\models\Activity;
    }

    public function set($message = null, $action = null, $type = null, $level = 1)
    {

        $ignoring = false;

        // Disable ignoring by URL and auth users for console application
        if (!$this->module->isConsole()) {

            $ignoringByRoute = false;
            if (is_array($this->module->ignoringRoutes)) {

                $url = Yii::$app->request->getUrl();
                foreach ($this->module->ignoringRoutes as $pattern) {
                    if (preg_match("/^" . preg_quote($pattern, "/") . "/", $url)) {
                        $ignoringByRoute = true;
                        break;
                    }
                }

            }

            $ignoringByUser = false;
            if (is_array($this->module->ignoringUsers)) {

                $user_id = $this->model->getUserID();
                if (in_array($user_id, $this->module->ignoringUsers))
                    $ignoringByUser = true;

            }


            $ignoringByIp = false;
            if (is_array($this->module->ignoringIp)) {

                $user_ip = $this->model->getUserIp();
                if (in_array($user_ip, $this->module->ignoringIp))
                    $ignoringByIp = true;

            }

            $ignoring = (
                $ignoringByRoute ||
                $ignoringByUser ||
                $ignoringByIp
            );
        }

        if (!$ignoring && $this->model->setActivity($message, $action, $type, $level)) {
            return true;
        }

        return false;
    }

}

?>