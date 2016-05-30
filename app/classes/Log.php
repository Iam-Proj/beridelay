<?php namespace System\Models;

use Carbon\Carbon;
/**
 * Лог событий
 * @package System\Models
 */
class Log extends Collection
{

    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
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
        $this->created_at = Carbon::now()->toDateTimeString();
    }

    public function afterFetch()
    {
        $this->created_at = new Carbon($this->created_at);
        $this->old_value = json_decode($this->old_value, true);
        $this->new_value = json_decode($this->new_value, true);
    }

    public function beforeSave()
    {
        $this->old_value = json_encode($this->old_value);
        $this->new_value = json_encode($this->new_value);
    }

    public static function log($object, $action, $object_id = null, $old_value = null, $new_value = null)
    {
        $log = new static();
        $log->object = $object;
        $log->object_id = $object_id;
        $log->action = $action;
        $log->old_value = $old_value;
        $log->new_value = $new_value;
        $log->save();
    }
    
}
