[![Progress](https://img.shields.io/badge/required-Yii2_v2.0.13-blue.svg)](https://packagist.org/packages/yiisoft/yii2) [![Github all releases](https://img.shields.io/github/downloads/wdmg/yii2-activity/total.svg)](https://GitHub.com/wdmg/yii2-activity/releases/) [![GitHub version](https://badge.fury.io/gh/wdmg%2Fyii2-activity.svg)](https://github.com/wdmg/yii2-activity) ![Progress](https://img.shields.io/badge/progress-in_development-red.svg) [![GitHub license](https://img.shields.io/github/license/wdmg/yii2-activity.svg)](https://github.com/wdmg/yii2-activity/blob/master/LICENSE)

# Yii2 Activity Module
User activity tracking system for Yii2

# Requirements 
* PHP 5.6 or higher
* Yii2 v.2.0.20 and newest
* [Yii2 Base](https://github.com/wdmg/yii2-base) module (required)
* [Yii2 Users](https://github.com/wdmg/yii2-users) module (required)

# Installation
To install the module, run the following command in the console:

`$ composer require "wdmg/yii2-activity:dev-master"`

After configure db connection, run the following command in the console:

`$ php yii activity/init`

And select the operation you want to perform:
  1) Apply all module migrations
  2) Revert all module migrations

# Migrations
In any case, you can execute the migration and create the initial data, run the following command in the console:

`$ php yii migrate --migrationPath=@vendor/wdmg/yii2-activity/migrations`

# Configure

To add a module to the project, add the following data in your configuration file:

    'modules' => [
        ...
        'activity' => [
            'class' => 'wdmg\activity\Module',
            'routePrefix' => 'admin'
        ],
        ...
    ],

# Usecase
Use the `setActivity($message = null, $action = null, $type = null, $level = 1)` method to log events and user actions, you can use the construction

    <?php
    
        if($model->login()) {
            $activity = new Activity;
            $activity->setActivity('User has successfully login.', 'login', 'info', 2);
            ...
        }
        
        // or from component
        
        if($model->login()) {
            Yii::$app->activity->set('User has successfully login.', 'login', 'info', 2);
            ...
        }
        
    ?>


# Routing
Use the `Module::dashboardNavItems()` method of the module to generate a navigation items list for NavBar, like this:

    <?php
        echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
            'label' => 'Modules',
            'items' => [
                Yii::$app->getModule('activity')->dashboardNavItems(),
                ...
            ]
        ]);
    ?>

# Status and version [in progress development]
* v.1.1.8 - Fixed deprecated class declaration
* v.1.1.7 - Added extra options to composer.json and navbar menu icon
* v.1.1.6 - Added choice param for non interactive mode