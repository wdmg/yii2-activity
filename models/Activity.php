<?php

namespace wdmg\activity\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class Activity extends ActiveRecord
{

    const LOG_TYPE_INFO = 'info';
    const LOG_TYPE_ERROR = 'error';
    const LOG_TYPE_SUCCESS = 'success';
    const LOG_TYPE_WARNING = 'warning';
    const LOG_SUSTEM_ACTIVITY = 'System';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_by'], 'integer'],
            [['message', 'created_at', 'action', 'type', 'metadata'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $rules = [
            'id' => Yii::t('app/modules/activity', 'ID'),
            'type' => Yii::t('app/modules/activity', 'Type'),
            'created_by' => Yii::t('app/modules/activity', 'User'),
            'created_at' => Yii::t('app/modules/activity', 'Date/time'),
            'metadata' => Yii::t('app/modules/activity', 'Metadata'),
            'action' => Yii::t('app/modules/activity', 'Action'),
            'message' => Yii::t('app/modules/activity', 'Message'),
        ];

        if(class_exists('\wdmg\users\models\Users') && isset(Yii::$app->modules['users']))
            $rules[] = [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \wdmg\users\models\Users::class, 'targetAttribute' => ['user_id' => 'id']];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%activity}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at']
                ],
            ],
            'metadata' => [
                'class' => 'wdmg\activity\behaviors\MetadataBehavior',
                'in_attribute' => 'action',
                'out_attribute' => 'metadata',
            ]
        ];
    }

    /**
     * Set in logs user activity.
     * @return bool whether the activity is saved successfully.
     */
    public function setActivity($message = null, $action = null, $type = null, $level = 1)
    {
        $model = Yii::createObject(__CLASS__);
        $model->type = ($type !== null) ? $type : self::LOG_TYPE_INFO;
        $model->message = ($message !== null) ? trim($message) : null;
        $model->created_by = (int)self::getUserID();
        $model->action = ($action !== null) ? $action : __METHOD__;

        // Write log activity to file
        if(intval($level) > 1) {
            if($model->type == 'danger')
                Yii::error('[action:'.$model->action.'] ' . $message, 'activity');
            else if($model->type == 'warning')
                Yii::warning('[action:'.$model->action.'] ' . $message, 'activity');
            else
                Yii::info('[action:'.$model->action.'] ' . $message, 'activity');
        }

        return $model->save();

    }

    /**
     * Get user ID
     *
     * @return int user id or null (0) if user guest
     */
    public static function getUserID()
    {
        $user = Yii::$app->user->identity;
        return $user && !(Yii::$app->user->isGuest) ? intval($user->id) : null;
    }

    /**
     * Get user IP
     *
     * @return int user remote IP
     */
    public static function getUserIp()
    {
        $remote_addr = inet_pton(Yii::$app->getRequest()->getUserIP());
        return long2ip(ip2long(inet_ntop($remote_addr)));
    }

    /**
     * Get username by user ID
     *
     * @param string $user_id
     * @return string $username|null
     */
    public static function getUsernameByID($user_id = null)
    {
        $user = null;
        if(class_exists('\wdmg\users\models\Users') && $user_id)
            $user = \wdmg\users\models\Users::findOne(['id' => intval($user_id)]);

        return $user ? $user->username : null;
    }


    /**
     * @return object of \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        if (class_exists('\wdmg\users\models\Users')) {
            return $this->hasOne(\wdmg\users\models\Users::class, ['id' => 'created_by']);
        } else {
            return $this->created_by;
        }
    }

    /**
     * Get user`s online count
     *
     * @return int count of online users
     */
    public static function getUsersOnline()
    {
        $timeout = Yii::$app->params['modules']['admin']['activity']['timeout'];
        $count = self::find()->where('created_at >= :timeout', [':timeout' => time() - intval($timeout)])->groupBy(['created_by'])->count();

        if(!$count)
            $count = 1;

        return $count;
    }
}
