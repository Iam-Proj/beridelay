<?php namespace System\Models;

use Carbon\Carbon;
use MongoId;
use MongoRegex;
use BeriDelay\Models\Token;
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
    
    public static function findByIds(array $ids)
    {
        $mongo_ids = [];
        foreach ($ids as $id) $mongo_ids[] = new MongoId($id);

        return static::find([
            'conditions' => [
                '_id' => [
                    '$in' => $mongo_ids
                ]
            ]
        ]);
    }

    public static function findByFilters($data)
    {
        $params = static::getFilters($data);
        if (isset($data['offset'])) $params['skip'] = $data['offset'];
        if (isset($data['count'])) $params['limit'] = $data['count'];
        if (isset($data['fields'])) {
            foreach($data['fields'] as $field) $params['fields'][$field] = true;
        }

        if (isset($data['sort'])) $sort_field = $data['sort'];
        $sort_direction = (!isset($data['sort_direction'])) ? 1 : $data['sort_direction'] == 0 ? 1 : -1;

        if (isset($sort_field)) $params['sort'] = [$sort_field => $sort_direction];

        // var_dump($params); exit;
        return static::find($params);
    }

    public static function countByFilters($data)
    {

    }

    public static function getFilters($data)
    {
        $params = [];
        if (isset($data['object'])) {
            $regex = new MongoRegex('/.*' . $data['object'] . '.*/i');
            $params['conditions']['object'] = $regex;
        }
        if (isset($data['action'])) {
            $regex = new MongoRegex('/.*' . $data['action'] . '.*/i');
            $params['conditions']['action'] = $regex;
        }
        if (isset($data['object_id'])) $params['conditions']['object_id'] = (int) $data['object_id'];
        if (isset($data['user_id'])) $params['conditions']['user_id'] = (int) $data['user_id'];

        return $params;
    }
    
}
