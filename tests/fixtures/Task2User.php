<?php namespace BeriDelay\Tests\Fixtures;

class Task2User extends FixtureBase
{
    public $table = 'task2user';

    public $key = ['task_id', 'user_id'];

    public $fixtures = [
        ['task_id' => 101, 'user_id' => 101, 'is_notice' => 0],
        ['task_id' => 101, 'user_id' => 102, 'is_notice' => 1],
        ['task_id' => 101, 'user_id' => 103, 'is_notice' => 0],
        ['task_id' => 102, 'user_id' => 102, 'is_notice' => 0],
        ['task_id' => 102, 'user_id' => 103, 'is_notice' => 1],
        ['task_id' => 103, 'user_id' => 103, 'is_notice' => 0],
    ];
}