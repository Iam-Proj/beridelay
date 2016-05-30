<?php namespace BeriDelay\Tests\Fixtures;

class Group extends FixtureBase
{
    public $fixtures = [
        ['id' => 101, 'name' => 'Основная группа', 'user_id' => 101],
        ['id' => 102, 'name' => 'Вложенная группа 1', 'user_id' => 102, 'group_id' => 101],
        ['id' => 103, 'name' => 'Вложенная группа 2', 'user_id' => 102, 'group_id' => 101],
        ['id' => 104, 'name' => 'Малая группа', 'user_id' => 102],
    ];
}