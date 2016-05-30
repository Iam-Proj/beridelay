<?php namespace BeriDelay\Tests\Fixtures;

use Phalcon\Di;
use Phalcon\Text;
use BeriDelay\Tests\Database\MysqlAdapter;
use BeriDelay\Tests\Database\MongoAdapter;

/**
 * Базовый класс фикстур. 
 * Автоматически заносит данные в БД, автоматически их удаляет. 
 * @package BeriDelay\Tests\Fixtures
 */
class FixtureBase
{
    /**
     * @var string Table name
     */
    protected $table = null;

    /**
     * @var string Database type (mysql, mongo)
     */
    protected $database = 'mysql';

    /**
     * @var array Fixtures array
     */
    protected $fixtures = [];

    /**
     * @var string|array Primary key (set array, if key contain more one fields)
     */
    protected $key = 'id';
    
    private $adapter = null;
    
    public function __construct()
    {
        switch ($this->database) {
            case 'mongo':
                $this->adapter = new MongoAdapter();
                break;
            case 'mysql':
            default:
                $this->adapter = new MysqlAdapter();
        }
        $this->adapter->table = $this->getTable();
        $this->adapter->key = $this->key;
    }

    /**
     * Apply fixtures
     */
    public function setUp()
    {
        $this->adapter->setUp($this->fixtures);
    }

    /**
     * Cancel fixtures
     */
    public function tearDown()
    {
        $this->adapter->tearDown($this->fixtures);
    }

    /**
     * Возвращает имя таблицы БД.
     * Если имя таблицы не задано - пытается получить его из имени класса
     * @return string
     */
    protected function getTable()
    {
        return $this->table !== null ? $this->table : $this->getTableFromFixtureName();
    }

    private function getTableFromFixtureName($fixture_name = null)
    {
        if ($fixture_name === null) $fixture_name = get_class($this);
        $fixture_name = basename($fixture_name);
        return Text::uncamelize($fixture_name);
    }
}