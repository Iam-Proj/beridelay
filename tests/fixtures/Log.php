<?php namespace BeriDelay\Tests\Fixtures;

class Log extends FixtureBase
{
    public $database = 'mongo';
    
    public $fixtures = [
        ['id' => 101, 'object' => 'user', 'action' => 'register', 'user_id' => 101],
        ['id' => 102, 'object' => 'user', 'action' => 'auth', 'user_id' => 101],
        ['id' => 103, 'object' => 'task', 'action' => 'changeName', 'old_value' => 'Задача', 'new_value' => 'Новая задача', 'user_id' => 102],
        ['id' => 104, 'object' => 'task', 'action' => 'changeName', 'old_value' => 'Задача', 'new_value' => 'Новая задача', 'user_id' => 103],
        ['id' => 105, 'object' => 'user', 'action' => 'register', 'user_id' => 103],
    ];
}