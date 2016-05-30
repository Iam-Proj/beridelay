<?php namespace BeriDelay\Tests\Fixtures;

class Task extends FixtureBase
{
    public $fixtures = [
        ['id' => 101, 'name' => 'Задача 1', 'user_id' => 101, 'project_id' => 101],
        ['id' => 102, 'name' => 'Задача 2', 'user_id' => 101, 'project_id' => 102],
        ['id' => 103, 'name' => 'Задача 3', 'user_id' => 102, 'project_id' => 102],
        ['id' => 104, 'name' => 'Задача 4', 'user_id' => 103, 'project_id' => 103],
        ['id' => 105, 'name' => 'Задача 5', 'user_id' => 103, 'project_id' => 104],
    ];
}