<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class HistoryMigration_101
 */
class HistoryMigration_101 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('history', array(
                'columns' => array(
                    new Column(
                        'id',
                        array(
                            'type' => Column::TYPE_INTEGER,
                            'unsigned' => true,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'size' => 11,
                            'first' => true
                        )
                    ),
                    new Column(
                        'user_id',
                        array(
                            'type' => Column::TYPE_INTEGER,
                            'unsigned' => true,
                            'notNull' => true,
                            'size' => 11,
                            'after' => 'id'
                        )
                    ),
                    new Column(
                        'task_id',
                        array(
                            'type' => Column::TYPE_INTEGER,
                            'unsigned' => true,
                            'notNull' => true,
                            'size' => 11,
                            'after' => 'user_id'
                        )
                    ),
                    new Column(
                        'target_id',
                        array(
                            'type' => Column::TYPE_INTEGER,
                            'unsigned' => true,
                            'notNull' => true,
                            'size' => 11,
                            'after' => 'task_id'
                        )
                    ),
                    new Column(
                        'description',
                        array(
                            'type' => Column::TYPE_TEXT,
                            'size' => 1,
                            'after' => 'target_id'
                        )
                    ),
                    new Column(
                        'comment',
                        array(
                            'type' => Column::TYPE_TEXT,
                            'size' => 1,
                            'after' => 'description'
                        )
                    ),
                    new Column(
                        'created_at',
                        array(
                            'type' => Column::TYPE_TIMESTAMP,
                            'default' => "CURRENT_TIMESTAMP",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'comment'
                        )
                    ),
                    new Column(
                        'updated_at',
                        array(
                            'type' => Column::TYPE_TIMESTAMP,
                            'default' => "CURRENT_TIMESTAMP",
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'created_at'
                        )
                    ),
                    new Column(
                        'deleted_at',
                        array(
                            'type' => Column::TYPE_TIMESTAMP,
                            'size' => 1,
                            'after' => 'updated_at'
                        )
                    )
                ),
                'indexes' => array(
                    new Index('PRIMARY', array('id'), 'PRIMARY'),
                    new Index('FK_history_user', array('user_id'), null),
                    new Index('FK_history_target', array('target_id'), null),
                    new Index('FK_history_task', array('task_id'), null)
                ),
                'references' => array(
                    new Reference(
                        'FK_history_target',
                        array(
                            'referencedSchema' => 'beridelay',
                            'referencedTable' => 'target',
                            'columns' => array('target_id'),
                            'referencedColumns' => array('id'),
                            'onUpdate' => 'RESTRICT',
                            'onDelete' => 'RESTRICT'
                        )
                    ),
                    new Reference(
                        'FK_history_task',
                        array(
                            'referencedSchema' => 'beridelay',
                            'referencedTable' => 'task',
                            'columns' => array('task_id'),
                            'referencedColumns' => array('id'),
                            'onUpdate' => 'RESTRICT',
                            'onDelete' => 'RESTRICT'
                        )
                    ),
                    new Reference(
                        'FK_history_user',
                        array(
                            'referencedSchema' => 'beridelay',
                            'referencedTable' => 'user',
                            'columns' => array('user_id'),
                            'referencedColumns' => array('id'),
                            'onUpdate' => 'RESTRICT',
                            'onDelete' => 'RESTRICT'
                        )
                    )
                ),
                'options' => array(
                    'TABLE_TYPE' => 'BASE TABLE',
                    'AUTO_INCREMENT' => '1',
                    'ENGINE' => 'InnoDB',
                    'TABLE_COLLATION' => 'utf8_general_ci'
                ),
            )
        );
    }

    /**
     * Run the migrations
     *
     * @return void
     */
    public function up()
    {

    }

    /**
     * Reverse the migrations
     *
     * @return void
     */
    public function down()
    {

    }

}
