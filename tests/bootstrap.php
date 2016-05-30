<?php

use Phalcon\Di;
use Phalcon\Di\FactoryDefault;

ini_set('display_errors',1);
error_reporting(E_ALL);

define('ROOT_PATH', __DIR__);
define('APP_PATH', __DIR__ . '/..');
define('PATH_LIBRARY', __DIR__ . '/../app/library/');
define('PATH_SERVICES', __DIR__ . '/../app/services/');
define('PATH_RESOURCES', __DIR__ . '/../app/resources/');
define('PATH_CONFIG', __DIR__ . '/../app/config/');

set_include_path(
    ROOT_PATH . PATH_SEPARATOR . get_include_path()
);

// Required for phalcon/incubator
include __DIR__ . "/../vendor/autoload.php";

global $config;
$config = include PATH_CONFIG . "config.php";
include PATH_CONFIG . "loader.php";

$di = new FactoryDefault();
Di::reset();
Di::setDefault($di);

