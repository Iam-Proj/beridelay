<?php namespace BeriDelay\Tests;

use Phalcon\Di;
use Phalcon\Test\UnitTestCase as PhalconTestCase;

abstract class UnitTestCase extends PhalconTestCase
{
    /**
     * @var \Voice\Cache
     */
    protected $_cache;

    /**
     * @var \Phalcon\Config
     */
    protected $_config;

    /**
     * @var bool
     */
    private static $_loaded = false;

    protected $object = null;
    protected $objectName = null;

    protected $fixtures = [];
    protected static $fixturesObjects = [];
    protected static $currentFixtures = null;

    public function setUp()
    {
        global $config;
        parent::setUp();

        // Загрузка дополнительных сервисов, которые могут потребоваться во время тестирования
        $di = Di::getDefault();

        // получаем любые компоненты DI, если у вас есть настройки, не забудьте передать их родителю

        $di->set('modelsManager', function() {
            return new \Phalcon\Mvc\Model\Manager();
        });

        $di->setShared('modelsMetadata', function () {
            return new \Phalcon\Mvc\Model\Metadata\Memory();
        });

        $di->setShared('db', function () use ($config) {
            $dbConfig = $config->database->main->toArray();
            $adapter = $dbConfig['adapter'];
            unset($dbConfig['adapter']);

            $class = 'Phalcon\Db\Adapter\Pdo\\' . $adapter;

            return new $class($dbConfig);
        });

        /**
         * MongoDB
         */
        $di->set('mongo', function () use ($config) {
            $dbConfig = $config->database->mongo->toArray();
            $connectString = 'mongodb://';
            if ($dbConfig['username']) $connectString .= $dbConfig['username'];
            if ($dbConfig['password']) $connectString .= ':' . $dbConfig['password'];
            if ($dbConfig['username']) $connectString .= '@';
            $connectString .= $dbConfig['host'];

            $mongo = new \MongoClient($connectString);
            return $mongo->selectDB($dbConfig['dbname']);
        }, true);

        $di->set('collectionManager', function(){
            return new \Phalcon\Mvc\Collection\Manager();
        }, true);

        $this->setDi($di);

        if ($this->objectName !== null) $this->object = new $this->objectName();

        $this->_loaded = true;
    }

    /**
     * Применяет указанные фикстуры. Игнорирует повторные вызовы одних и тех же фикстур.
     * Отменяет предыдущие примененные фикстуры перед применением новых.
     * @param string $fixturesName Имя фикстур
     * @param bool $reapply Насильно применяет фикстуры, даже если они уже были применены
     * @return bool
     */
    public function fixturesApply($fixturesName, $reapply = false)
    {
        if (!isset($this->fixtures[$fixturesName])) return false;
        if ($fixturesName == self::$currentFixtures && !$reapply) return false;

        self::$currentFixtures = $fixturesName;

        $objects = array_reverse(self::$fixturesObjects);
        foreach ($objects as &$object) {
            $object->tearDown();
            $object = null;
        }

        self::$fixturesObjects = [];

        foreach ($this->fixtures[$fixturesName] as $fixture) {
            $className = 'BeriDelay\Tests\Fixtures\\' . $fixture;
            $object = new $className();
            $object->setUp();
            self::$fixturesObjects[] = $object;
        }
    }

    public function tearDown()
    {
        $this->object = null;
    }

    public static function tearDownAfterClass()
    {
        $objects = array_reverse(self::$fixturesObjects);
        foreach ($objects as &$object) {
            $object->tearDown();
            $object = null;
        }
    }

    /**
     * Проверка на то, что тест правильно настроен
     *
     * @throws \PHPUnit_Framework_IncompleteTestError;
     */
    public function __destruct()
    {
        if (!$this->_loaded) {
            throw new \PHPUnit_Framework_IncompleteTestError('Please run parent::setUp().');
        }
    }
}