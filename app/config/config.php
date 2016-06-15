<?php

defined('APP_PATH') || define('APP_PATH', realpath('.'));

return new \Phalcon\Config(array(
    'database' => array(
        'adapter'     => 'Mysql',
        'host'        => getenv('DATABASE_HOST'),
        'username'    => getenv('DATABASE_USERNAME'),
        'password'    => getenv('DATABASE_PASSWORD'),
        'dbname'      => getenv('DATABASE_NAME'),
        'options'     => [PDO::ATTR_TIMEOUT => getenv('DATABASE_CONNECT_TIMEOUT')],
        'charset'     => 'utf8',
    ),
    'mongo' => array(
        'host' => getenv('MONGO_HOST'),
        'username' => getenv('MONGO_USERNAME'),
        'password' => getenv('MONGO_PASSWORD'),
        'dbname' => getenv('MONGO_NAME')
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
        'logsDir'        => APP_PATH . '/logs/',
        'testsDir'       => APP_PATH . '/tests/',
        'publicDir'      => APP_PATH . '/public/',
        'uploadDir'      => APP_PATH . '/public/upload/',
        'baseUri'        => '/',
        'uploadUri'      => '/public/upload/',
    ),
    'timeformat' => array(
        'timezone' => getenv('TIMEFORMAT_TIMEZONE'),
        'locale' => getenv('TIMEFORMAT_LOCALE'),
        'localeCarbon' => getenv('TIMEFORMAT_LOCALECARBON')
    ),
    'environment' => getenv('ENVIRONMENT'),
));
