<?php namespace BeriDelay\Tests\Fixtures;

class User extends FixtureBase
{
    public $fixtures = [
        ['id' => 101, 'name' => 'Павел', 'email' => 'pavel@test.ru', 'password' => '12345', 'is_admin' => 0],
        ['id' => 102, 'name' => 'Игорь', 'email' => 'igor@test.ru', 'password' => '12345', 'is_admin' => 1],
        ['id' => 103, 'name' => 'Елена', 'email' => 'elena@test.ru', 'password' => '12345', 'is_admin' => 0],
    ];
}