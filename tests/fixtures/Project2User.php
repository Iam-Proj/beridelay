<?php namespace BeriDelay\Tests\Fixtures;

class Project2User extends FixtureBase
{
    public $table = 'project2user';

    public $key = ['project_id', 'user_id'];

    public $fixtures = [
        ['project_id' => 101, 'user_id' => 101, 'is_notice' => 1],
        ['project_id' => 101, 'user_id' => 103, 'is_notice' => 0],
        ['project_id' => 102, 'user_id' => 101, 'is_notice' => 0],
        ['project_id' => 102, 'user_id' => 102, 'is_notice' => 1],
        ['project_id' => 102, 'user_id' => 103, 'is_notice' => 0],
        ['project_id' => 103, 'user_id' => 102, 'is_notice' => 0],
        ['project_id' => 103, 'user_id' => 103, 'is_notice' => 0],
    ];
}