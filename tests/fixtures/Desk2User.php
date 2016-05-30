<?php namespace BeriDelay\Tests\Fixtures;

class Desk2User extends FixtureBase
{
    public $table = 'desk2user';

    public $key = ['desk_id', 'user_id'];

    public $fixtures = [
        ['desk_id' => 101, 'user_id' => 101, 'is_notice' => 0],
        ['desk_id' => 102, 'user_id' => 101, 'is_notice' => 0],
        ['desk_id' => 102, 'user_id' => 103, 'is_notice' => 0],
        ['desk_id' => 103, 'user_id' => 102, 'is_notice' => 1],
        ['desk_id' => 103, 'user_id' => 103, 'is_notice' => 0],
        ['desk_id' => 104, 'user_id' => 101, 'is_notice' => 1],
        ['desk_id' => 105, 'user_id' => 102, 'is_notice' => 0],
    ];
}