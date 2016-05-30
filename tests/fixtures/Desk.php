<?php namespace BeriDelay\Tests\Fixtures;

class Desk extends FixtureBase
{
    public $fixtures = [
        ['id' => 101, 'name' => 'Доска 1', 'user_id' => 101, 'is_open' => 0],
        ['id' => 102, 'name' => 'Доска 2', 'user_id' => 101, 'is_open' => 0],
        ['id' => 103, 'name' => 'Доска 3', 'user_id' => 102, 'is_open' => 1],
        ['id' => 104, 'name' => 'Доска 4', 'user_id' => 102, 'is_open' => 0],
        ['id' => 105, 'name' => 'Доска 5', 'user_id' => 103, 'is_open' => 1],
    ];
}