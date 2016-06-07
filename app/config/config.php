<?php

defined('APP_PATH') || define('APP_PATH', realpath('.'));

return new \Phalcon\Config(array(
    'database' => array(
        'main' => array(
            'adapter'     => 'Mysql',
            'host'        => 'localhost',
            'username'    => 'root',
            'password'    => '',
            'dbname'      => 'beridelay',
            'charset'     => 'utf8',
        ),
        'operational' => array(
            'host' => 'localhost',
            'username' => '',
            'password' => '',
            'dbname' => 'beridelay'
        )
    ),
    'application' => array(
        'classesDir'     => APP_PATH . '/app/classes/',
        'controllersDir' => APP_PATH . '/app/controllers/',
        'modelsDir'      => APP_PATH . '/app/models/',
        'exceptionsDir'  => APP_PATH . '/app/exceptions/',
        'migrationsDir'  => APP_PATH . '/app/migrations/',
        'viewsDir'       => APP_PATH . '/app/views/',
        'pluginsDir'     => APP_PATH . '/app/plugins/',
        'libraryDir'     => APP_PATH . '/app/library/',
        'cacheDir'       => APP_PATH . '/app/cache/',
        'logsDir'        => APP_PATH . '/app/logs/',
        'testsDir'       => APP_PATH . '/tests/',
        'publicDir'      => APP_PATH . '/public/',
        'uploadDir'      => APP_PATH . '/public/upload/',
        'baseUri'        => '/',
        'uploadUri'      => '/public/upload/',
    ),
    'timeformat' => array(
        'timezone' => 'Asia/Yekaterinburg',
        'locale' => 'ru_RU.UTF-8',
        'localeCarbon' => 'ru'
    )
));
