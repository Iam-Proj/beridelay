<?php namespace BeriDelay\Tests\Fixtures;

class Project extends FixtureBase
{
    public $fixtures = [
        ['id' => 101, 'name' => 'Основной проект', 'user_id' => 101],
        ['id' => 102, 'name' => 'Вложенный проект 1', 'user_id' => 102, 'project_id' => 101],
        ['id' => 103, 'name' => 'Вложенный проект 2', 'user_id' => 102, 'project_id' => 101],
        ['id' => 104, 'name' => 'Еще один проект', 'user_id' => 103],
    ];
}