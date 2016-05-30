<?php namespace BeriDelay\Tests\Fixtures;

class Comment extends FixtureBase
{
    public $fixtures = [
        ['id' => 101, 'text' => 'Комментарий 1', 'user_id' => 101, 'task_id' => 101],
        ['id' => 102, 'text' => 'Комментарий 2', 'user_id' => 102, 'task_id' => 102],
        ['id' => 103, 'text' => 'Комментарий 3', 'user_id' => 103, 'task_id' => 102],
        ['id' => 104, 'text' => 'Комментарий 4', 'user_id' => 101, 'task_id' => 103],
        ['id' => 105, 'text' => 'Комментарий 5', 'user_id' => 101, 'task_id' => 103],
        ['id' => 106, 'text' => 'Комментарий 6', 'user_id' => 102, 'task_id' => 103],
        ['id' => 107, 'text' => 'Комментарий 7', 'user_id' => 102, 'task_id' => 103],
    ];
}