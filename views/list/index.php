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

$this->title = $this->context->module->name;
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs(
    '
    let pjaxInterval, freezTimeout;
    let pjaxState = true;
    
    function reloadPJax(pjaxContainer) {
        if (typeof pjaxContainer !== "undefined") {
            if (pjaxState) {
                pjaxState = false;
                console.log("$.pjax.reload()");
                $.pjax.reload({
                    container: pjaxContainer,
                    timeout: false
                });
            }
        }
    }
    
    function setAutoreloadPJax(pjaxContainer, updateInterval) {
        if (typeof pjaxContainer !== "undefined" && $("[name=\"auto-update\"]").prop("checked")) {
        
            if (typeof updateInterval == "undefined")
                updateInterval = 2000;
            
            pjaxInterval = setInterval(() => {
                reloadPJax(pjaxContainer);
                
                $(document).on("pjax:success", () => {
                    pjaxState = true;
                    clearInterval(pjaxInterval);
                    setTimeout(setAutoreloadPJax(pjaxContainer, updateInterval), updateInterval);
                });
                $(document).on("pjax:error", () => {
                    pjaxState = false;
                    clearInterval(pjaxInterval);
                    setTimeout(setAutoreloadPJax(pjaxContainer, updateInterval), (updateInterval * 2));
                });
            }, updateInterval);
        }
    }
    
    $("body").on("click", () => {
        pjaxState = false;
        clearInterval(pjaxInterval);
        clearTimeout(freezTimeout);
        freezTimeout = setTimeout(() => {
            pjaxState = true;
            autoreloadPJax();
        }, 2000);
    });
    
    $(document).delegate("[name=\"auto-update\"]", "change", (event) => {
        if (event.target.checked) {
            pjaxState = true;
            autoreloadPJax();
        } else {
            pjaxState = false;
        }
    });
    
    function autoreloadPJax() {
        setAutoreloadPJax("#activityAjax", 5000);
    }
    
    autoreloadPJax();
    
    ', \yii\web\View::POS_READY
);

?>

<div class="page-header">
    <h1>
        <?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small>
    </h1>
</div>
<div class="activity-index">
    <?php Pjax::begin([
        'id' => 'activityAjax',
        'timeout' => 5000
    ]); ?>
    <div class="pull-right">
        <?= Html::checkbox('auto-update', true, [
            'label' => Yii::t('app/modules/activity', '- Live auto-update'),
        ])?>
    </div>
    <?= GridView::widget([
            'dataProvider' => $activity,
            'layout' => '{summary}<br\/>{items}<br\/>{summary}<br\/><div class="text-center">{pager}</div>',
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
                    "format" => "html",
                    'value' => function($model, $indx) {
                        return $model->message;
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

                        $content = '<div>';
                        $metadata = unserialize($model->metadata);
                        if (count($metadata) > 0 && is_array($metadata)) {
                            foreach($metadata as $key => $value) {
                                $content .= '<b>'.$key.'</b>&nbsp;'.var_export($value, true).'<br/>';
                            }
                        }
                        $content .= '</div>';

                        return Html::a('<span class="fa fa-fw fa-ellipsis-v"></span></a>', '#', [
                            'data' => [
                                'toggle' => 'popover',
                                'content' => $content,
                                'html' => 'true',
                                'template' => '<div class="popover" role="tooltip" style="max-width: auto !important;"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
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
            ],
            'pager' => [
                'options' => [
                    'class' => 'pagination',
                ],
                'maxButtonCount' => 5,
                'activePageCssClass' => 'active',
                'prevPageCssClass' => 'prev',
                'nextPageCssClass' => 'next',
                'firstPageCssClass' => 'first',
                'lastPageCssClass' => 'last',
                'firstPageLabel' => Yii::t('app/modules/activity', 'First page'),
                'lastPageLabel'  => Yii::t('app/modules/activity', 'Last page'),
                'prevPageLabel'  => Yii::t('app/modules/activity', '&larr; Prev page'),
                'nextPageLabel'  => Yii::t('app/modules/activity', 'Next page &rarr;')
            ],
        ]);
        Pjax::end();
    ?>
</div>

<?php echo $this->render('../_debug'); ?>
