<?php

$loader = new \Phalcon\Loader();

$loader->registerNamespaces(
    array(
        'System\Models'             => $config->application->classesDir,
        'System\Controllers'        => $config->application->classesDir,
        'System\Helpers'            => $config->application->classesDir . 'helpers/',
        'System\Traits'             => $config->application->classesDir . 'traits/',
        'System\Behaviors'          => $config->application->classesDir . 'behaviors/',
        'System\Exceptions'         => $config->application->classesDir . 'exceptions/',

        'BeriDelay\Controllers'     => $config->application->controllersDir,
        'BeriDelay\Models'          => $config->application->modelsDir,
        'BeriDelay\Exceptions'      => $config->application->exceptionsDir,
        'BeriDelay\Tests'           => $config->application->testsDir,
        'BeriDelay\Tests\Models'    => $config->application->testsDir . 'unit/',
        'BeriDelay\Tests\Fixtures'  => $config->application->testsDir . 'fixtures/',
        'BeriDelay\Tests\Database'  => $config->application->testsDir . 'database/',
        'Carbon\Carbon'
    )
)->register();
