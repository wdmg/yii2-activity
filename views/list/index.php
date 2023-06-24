<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use wdmg\widgets\SelectInput;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel wdmg\activity\models\Activity */
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
        var data = {"autoupdate": event.target.checked};
        $.ajax({
            type: "POST",
            url: "autoupdate",
            data: data,
            dataType: "json",
            complete: function(data) {
                if(data) {
                    if (data.status == 200 && data.responseJSON.success) {
                    
                        if (data.autoupdate !== data.responseJSON.autoupdate)
                            window.location.reload();
                        
                        return true;
                    }
                }
            }
        });
    
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
        <?= Html::checkbox('auto-update', $this->params['auto-update'], [
            'label' => Yii::t('app/modules/activity', '- Live auto-update'),
        ])?>
    </div>
    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => '{summary}<br\/>{items}<br\/>{summary}<br\/><div class="text-center">{pager}</div>',
            'columns' => [
                [
                    "attribute" => "id",
                    'value' => 'id',
                ],
                [
                    "attribute" => "created_at",
                    'value' => function($searchModel, $indx) {
                        return Yii::$app->getFormatter()->asDatetime($searchModel->created_at, "dd.MM.yyyy hh:mm:ss");
                    },
                ],
                [
                    "attribute" => "action",
                    'value' => 'action',
                ],
                [
                    "attribute" => "message",
                    "format" => "html",
                    'value' => function($searchModel, $indx) {
                        return $searchModel->message;
                    },
                ],
	            [
		            'attribute' => 'type',
		            'format' => 'html',
		            'filter' => SelectInput::widget([
			            'model' => $searchModel,
			            'attribute' => 'type',
			            'items' => $searchModel->getTypesList(true),
			            'options' => [
				            'id' => 'activity-type',
				            'class' => 'form-control'
			            ]
		            ]),
		            'headerOptions' => [
			            'class' => 'text-center'
		            ],
		            'contentOptions' => [
			            'class' => 'text-center'
		            ],
		            'value' => function($data) {
			            if ($data->type == $data::LOG_TYPE_ERROR)
                            return '<span class="label label-danger">'.Yii::t('app/modules/activity', 'Error').'</span>';
			            else if ($data->type == $data::LOG_TYPE_INFO)
                            return '<span class="label label-info">'.Yii::t('app/modules/activity', 'Info').'</span>';
                        else if ($data->type == $data::LOG_TYPE_SUCCESS)
                            return '<span class="label label-success">'.Yii::t('app/modules/activity', 'Success').'</span>';
                        else if ($data->type == $data::LOG_TYPE_WARNING)
                            return '<span class="label label-danger">'.Yii::t('app/modules/activity', 'Warning').'</span>';
                        else
	                        return $data->type;
		            }
	            ],
                [
                    "attribute" => "created_by",
	                'filter' => SelectInput::widget([
		                'model' => $searchModel,
		                'attribute' => 'created_by',
		                'items' => $searchModel->getUsersList(true),
		                'options' => [
			                'id' => 'activity-created-by',
			                'class' => 'form-control'
		                ]
	                ]),
                    'value' => function($searchModel, $indx) {
                        $username = $searchModel->getUsernameByID($searchModel->created_by);
                        if($username)
                            return $username;
                        else
                            return $searchModel::LOG_SUSTEM_ACTIVITY;
                    },
                ],
                [
                    "attribute" => "metadata",
                    'format' => 'raw',
                    'filter' => false,
                    'value' => function($searchModel) {

                        $content = '<div>';
                        $metadata = unserialize($searchModel->metadata);
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
            'rowOptions' => function ($searchModel, $index, $widget, $grid) {
                if($searchModel->type == 'info')
                    return ['class' => 'info'];
                elseif ($searchModel->type == 'error')
                    return ['class' => 'danger'];
                elseif ($searchModel->type == 'warning')
                    return ['class' => 'warning'];
                elseif ($searchModel->type == 'success')
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
