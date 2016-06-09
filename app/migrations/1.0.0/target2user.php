<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class Target2userMigration_102
 */
class Target2userMigration_102 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('target2user', array(
                'columns' => array(
                    new Column(
                        'target_id',
                        array(
                            'type' => Column::TYPE_INTEGER,
                            'unsigned' => true,
                            'notNull' => true,
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
                            'after' => 'target_id'
                        )
                    ),
                    new Column(
                        'showed',
                        array(
                            'type' => Column::TYPE_INTEGER,
                            'default' => "0",
                            'unsigned' => true,
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'user_id'
                        )
                    ),
                    new Column(
                        'started',
                        array(
                            'type' => Column::TYPE_INTEGER,
                            'default' => "0",
                            'unsigned' => true,
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'showed'
                        )
                    )
                ),
                'indexes' => array(
                    new Index('PRIMARY', array('target_id', 'user_id'), 'PRIMARY'),
                    new Index('FK_target2user_user', array('user_id'), null)
                ),
                'options' => array(
                    'TABLE_TYPE' => 'BASE TABLE',
                    'AUTO_INCREMENT' => '',
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
