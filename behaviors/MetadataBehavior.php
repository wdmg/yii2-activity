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
        $metadata = '';
        if(($this->owner->{$this->in_attribute} == 'login') || ($this->owner->{$this->in_attribute} == 'logout')) {
            $metadata = [
                'user_ip' => Activity::getUserIp()
            ];
        }
        $this->owner->{$this->out_attribute} = serialize($metadata);
    }

}