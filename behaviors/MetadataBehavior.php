<?php

namespace wdmg\activity\behaviors;

use wdmg\activity\models\Activity;
use yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class MetadataBehavior extends Behavior
{
    public $in_attribute = 'action';
    public $out_attribute = 'metadata';

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'getMetaData'
        ];
    }

    public function getMetaData($event)
    {
        if (!Yii::$app->request->isConsoleRequest) {
            $metadata = [
                'uri' => Yii::$app->request->getAbsoluteUrl(),
                'refferer' => Yii::$app->request->getReferrer(),
                'status' => Yii::$app->response->getStatusCode(),
                'route' => Yii::$app->requestedRoute
            ];

            if (($this->owner->{$this->in_attribute} == 'login') || ($this->owner->{$this->in_attribute} == 'logout')) {
                $metadata[] = [
                    'user_ip' => Activity::getUserIp()
                ];
            }

            $this->owner->{$this->out_attribute} = serialize($metadata);
        } else {
            $this->owner->{$this->out_attribute} = serialize([]);
        }
    }

}