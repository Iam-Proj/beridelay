<?php namespace System\Models;

use Carbon\Carbon;
use MongoRegex;

/**
 * Лог событий
 * @package System\Models
 */
class Log extends Collection
{
    /**
     * @var Carbon
     */
    public $created_at;

    /**
     * @var integer
     */
    public $user_id;

    /**
     * @var string
     */
    public $object;

    /**
     * @var integer
     */
    public $object_id;

    /**
     * @var string
     */
    public $action;

    /**
     * @var string
     */
    public $old_value;

    /**
     * @var string
     */
    public $new_value;

    public function beforeCreate()
    {
        $this->created_at = new \MongoDate(time());
    }

    public function beforeSave()
    {
        $this->object_id = (int) $this->object_id;
    }

    public static function log($object, $action, $object_id = null, $old_value = null, $new_value = null)
    {
        $log = new static();

        $di = \Phalcon\Di::getDefault();

        if ($di->has('user')) {
            $user = $di->get('user');
            $log->user_id = (int) $user->id;
        }

        $log->object = $object;
        $log->object_id = (int) $object_id;
        $log->action = $action;
        $log->old_value = $old_value;
        $log->new_value = $new_value;
        $log->save();
    }

    public static function getFilters($data, $params = [])
    {
        if (isset($data['object'])) $params['conditions']['object'] = $regex = new MongoRegex('/.*' . $data['object'] . '.*/i');
        if (isset($data['action'])) $params['conditions']['action'] = $regex = new MongoRegex('/.*' . $data['action'] . '.*/i');
        if (isset($data['object_id'])) $params['conditions']['object_id'] = (int) $data['object_id'];
        if (isset($data['user_id'])) $params['conditions']['user_id'] = (int) $data['user_id'];

        $params = parent::getFilters($data, $params);

        return $params;
    }
    
}
