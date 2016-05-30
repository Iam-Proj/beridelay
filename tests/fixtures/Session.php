<?php namespace BeriDelay\Tests\Fixtures;

class Session extends FixtureBase
{
    public $database = 'mongo';

    public $fixtures = [
        ['id' => 101, 'user_id' => 101, 'ip' => '192.168.0.1'],
        ['id' => 102, 'user_id' => 101, 'ip' => '192.168.0.1'],
        ['id' => 103, 'user_id' => 102, 'ip' => '192.168.0.2'],
        ['id' => 104, 'user_id' => 103, 'ip' => '192.168.0.5'],
        ['id' => 105, 'user_id' => 103, 'ip' => '192.168.0.5'],
        ['id' => 106, 'user_id' => 103, 'ip' => '192.168.0.6'],
    ];
}