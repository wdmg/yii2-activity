<?php

namespace wdmg\activity\components;


/**
 * Yii2 Activity
 *
 * @category        Component
 * @version         1.1.9
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

    protected $model;

    /**
     * Initialize the component
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->model = new \wdmg\activity\models\Activity;
    }

    public function set($message = null, $action = null, $type = null, $level = 1)
    {
        if ($this->model->setActivity($message, $action, $type, $level)) {
            return true;
        }
        return false;
    }

}

?>