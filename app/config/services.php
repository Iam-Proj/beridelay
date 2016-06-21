<?php
/**
 * Services are globally registered in this file
 *
 * @var \Phalcon\Config $config
 */

use Phalcon\Logger;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Flash\Direct as Flash;
use Phalcon\Mvc\Dispatcher;
use Carbon\Carbon;
use Phalcon\Logger\Adapter\File as FileLogger;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Events\Event;
use Phalcon\Db\Profiler as DbProfiler;

//Устанавливаем системную локаль
setlocale(LC_TIME, 'ru_RU.UTF-8');
//Устанавливаем часовой пояс
date_default_timezone_set($config->timeformat->timezone);
//Устанавливаем локаль для Carbon
Carbon::setLocale($config->timeformat->localeCarbon);
Carbon::setToStringFormat('U');

/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new FactoryDefault();

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->setShared('url', function () use ($config) {
    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);

    return $url;
});

/**
 * Setting up the view component
 */
$di->setShared('view', function () use ($config) {

    $view = new View();

    $view->setViewsDir($config->application->viewsDir);

    $view->registerEngines(array(
        '.volt' => function ($view, $di) use ($config) {

            $volt = new VoltEngine($view, $di);

            $volt->setOptions(array(
                'compiledPath' => $config->application->cacheDir,
                'compiledSeparator' => '_'
            ));

            return $volt;
        },
        '.phtml' => 'Phalcon\Mvc\View\Engine\Php'
    ));

    return $view;
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () use ($config, $di) {
    $dbConfig = $config->database->toArray();
    $adapter = $dbConfig['adapter'];
    unset($dbConfig['adapter']);

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $adapter;

    $eventsManager = new EventsManager();

    $logger = new FileLogger($config->application->logsDir . "debug.log");
    $profiler = new DbProfiler();

    // Слушаем все события базы данных
    $eventsManager->attach('db', function ($event, $connection) use ($logger, $profiler) {
        if ($event->getType() == 'beforeQuery') {
            $logger->log($connection->getSQLStatement(), Logger::DEBUG);
            $profiler->startProfile($connection->getSQLStatement());
        }
        if ($event->getType() == 'afterQuery') {
            $profiler->stopProfile();
            $profile = $profiler->getLastProfile();
            $logger->log("Total Elapsed Time: " . $profile->getTotalElapsedSeconds(), Logger::INFO);
        }
    });

    $connection = new $class($dbConfig);
    //$connection = new \Phalcon\Db\Adapter\Pdo\Mysql();

    $connection->setEventsManager($eventsManager);

    return $connection;
});

/**
 * MongoDB
 */
$di->set('mongo', function () use ($config) {
    $dbConfig = $config->mongo;
    $connectString = 'mongodb://';
    if ($dbConfig['username']) $connectString .= $dbConfig->username;
    if ($dbConfig['password']) $connectString .= ':' . $dbConfig->password;
    if ($dbConfig['username']) $connectString .= '@';
    $connectString .= $dbConfig->host;

    $mongo = new MongoClient($connectString);
    return $mongo->selectDB($dbConfig->dbname);
}, true);

$di->set('collectionManager', function(){
    return new Phalcon\Mvc\Collection\Manager();
}, true);


/**
 * Register the session flash service with the Twitter Bootstrap classes
 */
$di->set('flash', function () {
    return new Flash(array(
        'error'   => 'alert alert-danger',
        'success' => 'alert alert-success',
        'notice'  => 'alert alert-info',
        'warning' => 'alert alert-warning'
    ));
});

/**
 * Start the session the first time some component request the session service
 */
$di->setShared('session', function () {
    $session = new SessionAdapter();
    $session->start();

    return $session;
});

// Регистрация диспетчера
$di->setShared('dispatcher', function () {

    // Создаем EventsManager
    $eventsManager = new EventsManager();

    // Добавляем слушателся
    $eventsManager->attach("dispatch:beforeException", function (Event $event, Dispatcher $dispatcher, Exception $exception) {

        $controller = $dispatcher->getControllerName();
        $method = $dispatcher->getActionName();

        switch ($exception->getCode()) {
            case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                $dispatcher->forward([
                    'controller' => 'index',
                    'action' => 'show404',
                    'params' => [$method, $controller]
                ]);

                return false;

            case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                $dispatcher->forward([
                    'action' => 'show404',
                    'params' => [$method]
                ]);

                return false;
        }
    });

    $dispatcher = new Dispatcher();
    $dispatcher->setDefaultNamespace('BeriDelay\Controllers');

    // Назначаем диспетчеру EventManager
    $dispatcher->setEventsManager($eventsManager);
    return $dispatcher;
});

$di->set('config', $config, true);

\Phalcon\Mvc\Model::setup(array(
    'notNullValidations' => false
));