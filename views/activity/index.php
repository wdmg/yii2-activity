<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use wdmg\widgets\SelectInput;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $model wdmg\activity\models\Activity */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/modules/activity', 'User activity');
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs(
'setInterval(function(){
        $.pjax.reload({container:\'#activityAjax\'});
    }, 5000);', \yii\web\View::POS_READY
);

?>

<div class="page-header">
    <h1>
        <?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small>
    </h1>
</div>
<div class="activity-index">
    <?php
        Pjax::begin([
            'id' => 'activityAjax',
            'timeout' => 5000
        ]);
        echo GridView::widget([
            'dataProvider' => $activity,
            'columns' => [
                [
                    "attribute" => "id",
                    'value' => 'id',
                ],
                [
                    "attribute" => "created_at",
                    'value' => function($model, $indx) {
                        return Yii::$app->getFormatter()->asDatetime($model->created_at, "dd.MM.yyyy hh:mm:ss");
                    },
                ],
                [
                    "attribute" => "action",
                    'value' => 'action',
                ],
                [
                    "attribute" => "message",
                    'value' => function($model, $indx) {
                        return unserialize($model['message']);
                    },
                ],
                [
                    "attribute" => "created_by",
                    'value' => function($model, $indx) {
                        $username = $model->getUsernameByID($model->created_by);
                        if($username)
                            return $username;
                        else
                            return $model::LOG_SUSTEM_ACTIVITY;
                    },
                ],
                [
                    "attribute" => "metadata",
                    'format' => 'raw',
                    'value' => function($model) {

                        $content = '';
                        $metadata = unserialize($model->metadata);
                        if(count($metadata) > 0 && is_array($metadata)) {
                            foreach($metadata as $key => $value) {
                                $content .= '<b>'.$key.'</b>&nbsp;'.$value.'<br/>';
                            }
                        }

                        return Html::a('<span class="fa fa-fw fa-ellipsis-v"></span></a>', '#', [
                            'data' => [
                                'toggle' => 'popover',
                                'content' => $content,
                                'placement' => 'auto left',
                                'pjax' => '0',
                            ]
                        ]);

                    },
                ],
            ],
            'rowOptions' => function ($model, $index, $widget, $grid) {
                if($model->type == 'info')
                    return ['class' => 'info'];
                elseif ($model->type == 'danger')
                    return ['class' => 'danger'];
                elseif ($model->type == 'warning')
                    return ['class' => 'warning'];
                elseif ($model->type == 'success')
                    return ['class' => 'success'];
                else
                    return [];
            },
            'tableOptions' => [
                'id' => 'activityList',
                'class' => 'table table-striped table-vertical table-bordered table-responsive'
            ]
        ]);
        Pjax::end();
    ?>
</div>

<?php echo $this->render('../_debug'); ?>
