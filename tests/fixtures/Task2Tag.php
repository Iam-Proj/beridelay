<?php namespace BeriDelay\Tests\Fixtures;

class Task2Tag extends FixtureBase
{
    public $table = 'task2tag';

    public $key = ['task_id', 'tag_id'];

    public $fixtures = [
        ['task_id' => 101, 'tag_id' => 101],
        ['task_id' => 102, 'tag_id' => 101],
        ['task_id' => 102, 'tag_id' => 102],
        ['task_id' => 103, 'tag_id' => 102],
        ['task_id' => 103, 'tag_id' => 104],
    ];
}