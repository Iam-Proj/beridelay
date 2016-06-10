<?php namespace System\Models;

use Phalcon\Mvc\Collection as PhalconCollection;
use Phalcon\Text;
use MongoId;
use MongoDate;
use System\Exceptions\ValidationException;

/**
 * Class Collection
 * Базовый класс для всех коллекций. Предоставляет интерфейс объявления связей между моделями (коллекциями).
 * Кроме того, реализаует связи типа ODM->ODM и ODM->ORM
 * @package System\Models
 */
class Collection extends PhalconCollection
{
    protected $table = null;

    /**
     * [
     *  $alias => [
     *      $ReferenceModel,
     *      'key' => $referencedFields,
     *      'otherKey' => $fields
     *  ]
     * ]
     */
    protected $hasMany = [];

    /**
     * [
     *  $alias => [
     *      $ReferenceModel,
     *      'key' => $referencedFields,
     *      'otherKey' => $fields
     *  ]
     * ]
     */
    protected $hasOne = [];

    /**
     * [
     *  '$alias' => [
     *      $ReferenceModel,
     *      'key' => $fields,
     *      'otherKey' => $referencedFields
     *  ]
     * ]
     */
    protected $belongsTo = [];

    /**
     * [
     *  '$alias' => [
     *      $ReferenceModel,
     *      'model' => $intermediateModel,
     *      'key' => $intermediateFields,
     *      'other_key' => $intermediateReferencedFields,
     *      'field' => $fields,
     *      'referencedField' => $referencedField
     *  ]
     * ]
     */
    protected $hasManyToMany = [];

    private $relations = [];

    protected function onConstruct()
    {
        if ($this->table !== null) $this->setSource($this->table);

        foreach ($this->hasMany as $alias => $relation) $this->createRelation('hasMany', $alias, $relation);
        foreach ($this->hasOne as $alias => $relation) $this->createRelation('hasOne', $alias, $relation);
        foreach ($this->belongsTo as $alias => $relation) $this->createRelation('belongsTo', $alias, $relation);
        foreach ($this->hasManyToMany as $alias => $relation) $this->createRelation('hasManyToMany', $alias, $relation);

    }

    protected function createRelation($type, $alias, $relation)
    {
        $referenceModel = array_shift($relation);
        $referencedField = isset($relation['key']) ? $relation['key'] : $this->getKeyFromModelName(get_class($this));
        $field = isset($relation['otherKey']) ? $relation['otherKey'] : 'id';

        switch ($type) {
            case 'hasMany':
            case 'hasOne':
                $reference = [
                    'type' => $type,
                    'referenceModel' => $referenceModel,
                    'key' => $referencedField,
                    'otherKey' => $field,
                ];
                break;
            case 'belongsTo':
                $reference = [
                    'type' => $type,
                    'referenceModel' => $referenceModel,
                    'key' => $field,
                    'otherKey' => $referencedField,
                ];
                break;
            //TODO: Реализовать поддержку ODM M2M ODM, ODM M2M ORM
            default: return;
        }


        if (is_subclass_of($referenceModel, 'Phalcon\Mvc\Collection')) {
            $reference['relationWith'] = 'collection';
        } else {
            $reference['relationWith'] = 'model';
        }

        $this->relations[$alias] = $reference;
    }

    protected function getKeyFromModelName($model_name)
    {
        $model_name = basename($model_name);
        return Text::uncamelize($model_name) . '_id';
    }

    public function __get($property)
    {
        if (!isset($this->relations[$property])) {
            $trace = debug_backtrace();
            trigger_error('Access to undefined property ' . get_class($this) . '::' . $property . ' in <b>' . $trace[0]['file'] . '</b> on line <b>' . $trace[0]['line'] . '</b>', E_USER_NOTICE);
            return null;
        }

        $relation = $this->relations[$property];

        $value = $this->$relation['otherKey'];
        if (is_numeric($value)) $value = $value + 0;

        $method = '';
        switch ($relation['type']) {
            case 'hasMany':
                $method = $relation['referenceModel'] . '::find';
                break;

            case 'hasOne':
            case 'belongsTo':
                $method = $relation['referenceModel'] . '::findFirst';
                break;
        }

        if ($relation['relationWith'] == 'collection') {
            return call_user_func_array($method, [
                [
                    [$relation['key'] => $value]
                ]
            ]);
        } else {
            return call_user_func_array($method, [
                $relation['key'] . ' = \'' . $value . '\''
            ]);
        }
    }

    public function __isset($property)
    {
        return isset($this->relations[$property]);
    }

    public function __call($name, $arguments)
    {
        $pos = stripos($name, 'count');
        $field = Text::uncamelize(substr($name, 5));

        if (!isset($this->relations[$field])) {
            $trace = debug_backtrace();
            trigger_error('Access to undefined method ' . get_class($this) . '::' . $name . ' in <b>' . $trace[0]['file'] . '</b> on line <b>' . $trace[0]['line'] . '</b>', E_USER_NOTICE);
            return null;
        }

        $relation = $this->relations[$field];

        $value = $relation['keyToInt'] ? (int) $this->$relation['otherKey'] : $this->$relation['otherKey'];

        $method = $relation['referenceModel'] . '::count';
        if ($relation['relationWith'] == 'collection') {
            return call_user_func_array($method, [
                [
                    [$relation['key'] => $value]
                ]
            ]);
        } else {
            return call_user_func_array($method, [
                $relation['key'] . ' = \'' . $value . '\''
            ]);
        }
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

        return static::find($params);
    }

    public static function countByFilters($data)
    {
        return static::count(static::getFilters($data));
    }

    public static function getFilters($data, $params = [])
    {
        if (isset($data['created_at'])) {
            $filter = $data['created_at'];

            if (isset($filter['less'])) {
                if (!is_numeric($filter['less']) || $filter['less'] < 1) throw new ValidationException(['format' => ['created_at.less' => 'timestamp']]);
                $time = new MongoDate($filter['less']);

                $params['conditions']['created_at']['$lte'] = $time;
            }
            if (isset($filter['more'])) {
                if (!is_numeric($filter['more']) || $filter['more'] < 1) throw new ValidationException(['format' => ['created_at.less' => 'timestamp']]);
                $time = new MongoDate($filter['more']);

                $params['conditions']['created_at']['$gte'] = $time;
            }
        }

        return $params;
    }
}