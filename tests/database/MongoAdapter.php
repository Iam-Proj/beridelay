<?php namespace BeriDelay\Tests\Database;

use Phalcon\Di;
use Phalcon\Text;

/**
 * Класс адаптера Mongo базы данных.
 * @package BeriDelay\Tests\Database
 */
class MongoAdapter extends DatabaseAdapter
{
    public function setUp($fixtures)
    {
        $di = Di::getDefault();
        $db = $di->getMongo();
        $collection = $db->selectCollection($this->table);

        foreach ($fixtures as $fixture) {
            $collection->insert($fixture);
        }
    }

    public function tearDown($fixtures)
    {
        $di = Di::getDefault();
        $db = $di->getMongo();
        $collection = $db->selectCollection($this->table);

        $fixtures = array_reverse($fixtures, true);

        foreach ($fixtures as $fixture) {
            $collection->remove($this->getCondition($fixture));
        }
    }

    private function getCondition($fixture)
    {
        if (is_array($this->key)) {
            $result = [];
            foreach ($this->key as $key) $result[$key] = $fixture[$key];
            return $result;
        } else {
            return [$this->key => $fixture[$this->key]];
        }
    }
}