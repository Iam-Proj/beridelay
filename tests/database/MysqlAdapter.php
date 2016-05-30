<?php namespace BeriDelay\Tests\Database;

use Phalcon\Di;
use Phalcon\Text;

/**
 * Rласс адаптера Mysql базы данных.
 * Для занесения тестовых данных в БД и удаления их оттуда.
 * @package BeriDelay\Tests\Database
 */
class MysqlAdapter extends DatabaseAdapter
{
    public function setUp($fixtures)
    {
        $di = Di::getDefault();
        $db = $di->getDb();

        foreach ($fixtures as $fixture) {
            $values = $fields = [];

            foreach ($fixture as $field => $value) {
                $values[] = $value;
                $fields[] = $field;
            }

            $db->insert($this->table, $values, $fields);
        }
    }

    public function tearDown($fixtures)
    {
        $di = Di::getDefault();
        $db = $di->getDb();

        $fixtures = array_reverse($fixtures, true);

        foreach ($fixtures as $fixture) {
            $db->delete($this->table, $this->getCondition($fixture));
        }
    }

    private function getCondition($fixture)
    {
        if (is_array($this->key)) {
            $result = [];
            foreach ($this->key as $key) {
                $result[] = $key . ' = "' . $fixture[$key] . '"';
            }
            return implode(' and ', $result);
        } else {
            return $this->key . ' = "' . $fixture[$this->key] . '"';
        }
    }
}