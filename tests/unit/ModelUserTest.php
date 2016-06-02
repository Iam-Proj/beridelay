<?php namespace BeriDelay\Tests\Models;

use BeriDelay\Models\User;
use BeriDelay\Tests\UnitTestCase;

class ModelUserTest extends UnitTestCase
{
    public $objectName = 'BeriDelay\Models\User';

    public $fixtures = [
        'user' => ['User'],
        'comments' => ['User', 'Project', 'Task', 'Comment'],
        'logs' => ['User', 'Log'],
        'sessions' => ['User', 'Session'],

        'master_desks' => ['User', 'Desk'],
        'master_groups' => ['User', 'Group'],
        'master_projects' => ['User', 'Project'],
        'master_tasks' => ['User', 'Project', 'Task'],

        'desks' => ['User', 'Desk', 'Desk2User'],
        'groups' => ['User', 'Group', 'Group2User'],
        'projects' => ['User', 'Project', 'Project2User'],
        'tasks' => ['User', 'Project', 'Task', 'Task2User'],
    ];

    public function testEmailFormat()
    {
        $this->object->name = 'Pavel';
        $this->object->email = 'dontworry';
        $this->assertFalse($this->object->validation());
    }

    public function testEmailRequired()
    {
        $this->object->name = 'Pavel';
        $this->object->email = '';
        $this->assertFalse($this->object->validation());
    }

    public function testEmailUnique()
    {
        $this->fixturesApply('user');

        $this->object->name = 'Pavel';
        $this->object->email = 'pavel@test.ru';
        $this->assertFalse($this->object->validation());
    }

    public function testNameRequired()
    {
        $this->object->name = '';
        $this->object->email = 'test@test.ru';
        $this->assertFalse($this->object->validation());
    }

    public function testNameFormat()
    {
        $this->object->email = 'test@test.ru';

        $this->object->name = 'd';
        $this->assertFalse($this->object->validation());
        $this->object->name = 'duhastduhastduhastduhastduhastduhastduhastduhastduhastduhastd';
        $this->assertFalse($this->object->validation());
    }

    public function testIsAdminFormat()
    {
        $this->object->name = 'Pavel';
        $this->object->email = 'test@test.ru';
        $this->object->is_admin = 2;

        $this->assertFalse($this->object->validation());
    }

    /**
     * @dataProvider relationCommentsProvider
     */
    /*public function testRelationComments($a, $result)
    {
        $this->fixturesApply('comments');

        $user = User::findFirst($a);

        $this->assertEquals($result, $user->countComments());
    }

    public static function relationCommentsProvider()
    {
        return [
            [101, 3],
            [103, 1],
        ];
    }

    /**
     * @dataProvider relationLogsProvider
     */
    /*public function testRelationLogs($a, $result)
    {
        $this->fixturesApply('logs');

        $user = User::findFirst($a);

        $this->assertEquals($result, $user->countLogs());
    }

    public static function relationLogsProvider()
    {
        return [
            [101, 2],
            [102, 1],
        ];
    }

    /**
     * @dataProvider relationSessionProvider
     */
    /*public function testRelationSession($a, $result)
    {
        $this->fixturesApply('sessions');

        $user = User::findFirst($a);

        $this->assertEquals($result, $user->countSessions());
    }

    public static function relationSessionProvider()
    {
        return [
            [101, 2],
            [103, 3],
        ];
    }

    /**
     * @dataProvider relationMasterDesksProvider
     */
    /*public function testRelationMasterDesks($a, $result)
    {
        $this->fixturesApply('master_desks');

        $user = User::findFirst($a);

        $this->assertEquals($result, $user->countMasterDesks());
    }

    public static function relationMasterDesksProvider()
    {
        return [
            [101, 2],
            [103, 1],
        ];
    }

    /**
     * @dataProvider relationMasterGroupsProvider
     */
    /*public function testRelationMasterGroups($a, $result)
    {
        $this->fixturesApply('master_groups');

        $user = User::findFirst($a);

        $this->assertEquals($result, $user->countMasterGroups());
    }

    public static function relationMasterGroupsProvider()
    {
        return [
            [101, 1],
            [102, 3],
        ];
    }

    /**
     * @dataProvider relationMasterProjectsProvider
     */
    /*public function testRelationMasterProjects($a, $result)
    {
        $this->fixturesApply('master_projects');

        $user = User::findFirst($a);

        $this->assertEquals($result, $user->countMasterProjects());
    }

    public static function relationMasterProjectsProvider()
    {
        return [
            [102, 2],
            [103, 1],
        ];
    }

    /**
     * @dataProvider relationMasterTasksProvider
     */
    /*public function testRelationMasterTasks($a, $result)
    {
        $this->fixturesApply('master_tasks');

        $user = User::findFirst($a);

        $this->assertEquals($result, $user->countMasterTasks());
    }

    public static function relationMasterTasksProvider()
    {
        return [
            [101, 2],
            [102, 1],
        ];
    }

    /**
     * @dataProvider relationDesksProvider
     */
    /*public function testRelationDesks($a, $result)
    {
        $this->fixturesApply('desks');

        $user = User::findFirst($a);

        $this->assertEquals($result, $user->countDesks());
    }

    public static function relationDesksProvider()
    {
        return [
            [101, 3],
            [103, 2],
        ];
    }

    /**
     * @dataProvider relationGroupsProvider
     */
    /*public function testRelationGroups($a, $result)
    {
        $this->fixturesApply('groups');

        $user = User::findFirst($a);

        $this->assertEquals($result, $user->countGroups());
    }
    
    public static function relationGroupsProvider()
    {
        return [
            [101, 1],
            [102, 2],
        ];
    }

    /**
     * @dataProvider relationProjectsProvider
     */
    /*public function testRelationProjects($a, $result)
    {
        $this->fixturesApply('projects');

        $user = User::findFirst($a);

        $this->assertEquals($result, $user->countProjects());
    }

    public static function relationProjectsProvider()
    {
        return [
            [101, 2],
            [103, 3],
        ];
    }

    /**
     * @dataProvider relationTasksProvider
     */
    /*public function testRelationTasks($a, $result)
    {
        $this->fixturesApply('tasks');

        $user = User::findFirst($a);

        $this->assertEquals($result, $user->countTasks());
    }

    public static function relationTasksProvider()
    {
        return [
            [101, 1],
            [103, 3],
        ];
    }*/

}