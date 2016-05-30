<?php namespace BeriDelay\Tests\Fixtures;

class Group2User extends FixtureBase
{
    public $table = 'group2user';

    public $key = ['group_id', 'user_id'];
    
    public $fixtures = [
        ['group_id' => 101, 'user_id' => 101, 'is_notice' => 0],
        ['group_id' => 101, 'user_id' => 102, 'is_notice' => 1],
        ['group_id' => 101, 'user_id' => 103, 'is_notice' => 0],
        ['group_id' => 102, 'user_id' => 102, 'is_notice' => 0],
        ['group_id' => 102, 'user_id' => 103, 'is_notice' => 1],
        ['group_id' => 103, 'user_id' => 103, 'is_notice' => 0],
    ];
}