[![Yii2](https://img.shields.io/badge/required-Yii2_v2.0.40-blue.svg)](https://packagist.org/packages/yiisoft/yii2)
[![Downloads](https://img.shields.io/packagist/dt/wdmg/yii2-activity.svg)](https://packagist.org/packages/wdmg/yii2-activity)
[![Packagist Version](https://img.shields.io/packagist/v/wdmg/yii2-activity.svg)](https://packagist.org/packages/wdmg/yii2-activity)
![Progress](https://img.shields.io/badge/progress-ready_to_use-green.svg)
[![GitHub license](https://img.shields.io/github/license/wdmg/yii2-activity.svg)](https://github.com/wdmg/yii2-activity/blob/master/LICENSE)

<img src="./docs/images/yii2-activity.png" width="100%" alt="Yii2 Activity" />

# Yii2 Activity Module
User activity tracking system for Yii2. You can use the extension component to register any activity.

This module is an integral part of the [Butterfly.СMS](https://butterflycms.com/) content management system, but can also be used as an standalone extension.

Copyrights (c) 2019-2021 [W.D.M.Group, Ukraine](https://wdmg.com.ua/)

# Requirements 
* PHP 5.6 or higher
* Yii2 v.2.0.40 and newest
* [Yii2 Base](https://github.com/wdmg/yii2-base) module (required)
* [Yii2 Users](https://github.com/wdmg/yii2-users) module (required)

# Installation
To install the module, run the following command in the console:

`$ composer require "wdmg/yii2-activity"`

After configure db connection, run the following command in the console:

`$ php yii activity/init`

And select the operation you want to perform:
  1) Apply all module migrations
  2) Revert all module migrations
  3) Clear users activity log

# Migrations
In any case, you can execute the migration and create the initial data, run the following command in the console:

`$ php yii migrate --migrationPath=@vendor/wdmg/yii2-activity/migrations`

# Configure

To add a module to the project, add the following data in your configuration file:

    'modules' => [
        ...
        'activity' => [
            'class' => 'wdmg\activity\Module',
            'routePrefix' => 'admin',
            'surfingActivity': false, // Log of web-surfing activity
            'backendSurfing': true, // Log of web-surfing activity by backend.
            'frontendSurfing': false, // Log of web-surfing activity by frontend.
            'ignoringRoutes': [], // Ignoring activity by request route
            'ignoringUsers': [], // Ignoring activity by user ID
            'ignoringIp': [] // Ignoring activity by user IP
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

# Status and version [ready to use]
* v.1.1.13 - Live auto-update for list
* v.1.1.12 - Update README.md, added clear users activity log from console
* v.1.1.11 - Update README.md and dependencies
* v.1.1.10 - Added properties, refactoring setActivity() method
* v.1.1.9 - Added pagination, up to date dependencies
* v.1.1.8 - Fixed deprecated class declaration
* v.1.1.7 - Added extra options to composer.json and navbar menu icon
* v.1.1.6 - Added choice param for non interactive mode