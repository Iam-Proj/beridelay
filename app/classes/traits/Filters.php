<?php namespace System\Traits;

use System\Exceptions\ValidationException;
/**
 * Трайт "Фильтры"
 * Позволяет фильтровать данные
 * @package System\Traits
 */
trait Filters
{
    public static $filterType = 0;

    /**
     * @param array $data
     * @param \Phalcon\Mvc\Model\Criteria|array $query
     * @return array
     */
    public static function get($data, $query = null)
    {
        if ($query == null) $query = static::getFilters($data);

        $sort_field = isset($data['sort']) ? $data['sort'] : 'created_at';
        $sort_order = isset($data['sort_direction']) ? $data['sort_direction'] == 1 ? 'desc' : 'asc' : 'asc';

        $query->orderBy($sort_field . ' ' . $sort_order);

        $rowCount = $query->columns('COUNT(*) as count')->execute()->toArray()[0]['count'];

        $columns = isset($data['fields']) ? array_intersect(static::$fields, $data['fields']) : static::$fields;
        $query->columns($columns);

        $count = isset($data['count']) ? $data['count'] : 100;

        $offset = 0;
        $page = 1;
        if (isset($data['page'])) {
            $offset = ($data['page'] - 1) * $count;
            $page = $data['page'];
        }
        
        if (isset($data['offset'])) {
            $offset = $data['offset'];
            $page = floor($offset / $count) + 1;
        }

        $query->limit($count, $offset);

        $result = $query->execute()->toArray();

        return [
            'result' => $result,
            'count' => (int) $rowCount,
            'offset' => (int) $offset,
            'page' => (int) $page,
            'pageCount' => ceil($rowCount / $count)
        ];

    }

    /**
     * @param array $data
     * @param \Phalcon\Mvc\Model\Criteria $query
     * @return \Phalcon\Mvc\Model\Criteria
     * @throws ValidationException
     */
    protected static function getFiltersBase($data, $query)
    {
        return $query;
    }

    /**
     * @param array $data
     * @return \Phalcon\Mvc\Model\Criteria
     * @throws ValidationException
     */
    public static function getFilters($data)
    {
        $query = static::getFiltersIds($data);
        if ($query->getWhere() != null) return $query;

        $query = static::getFiltersBase($data, $query);
        $query = static::getFiltersDates($data, $query);

        return $query;
    }
    /**
     * @param array $data
     * @param \Phalcon\Mvc\Model\Criteria|null $query
     * @return \Phalcon\Mvc\Model\Criteria
     * @throws ValidationException
     */
    protected static function getFiltersIds($data, $query = null)
    {
        if ($query === null) $query = static::query();

        if (isset($data['ids'])) {
            $ids = [];
            if (!is_array($data['ids'])) throw new ValidationException(['format' => ['ids' => 'array.integer']]);
            foreach ($data['ids'] as $id) if (is_numeric($id) && $id > 0) $ids[] = $id;
            if (!count($ids)) throw new ValidationException(['format' => ['ids' => 'array.integer']]);

            $query->inWhere('id', $ids);
        } elseif (isset($data['id'])) {
            if (!is_numeric($data['id']) && $data['id'] < 0)  throw new ValidationException(['format' => ['id' => 'integer']]);
            $query->where('id = :id:')->bind(['id' => $data['id']]);
        }

        return $query;
    }

    /**
     * @param array $data
     * @param \Phalcon\Mvc\Model\Criteria|null $query
     * @return \Phalcon\Mvc\Model\Criteria
     * @throws ValidationException
     */
    protected static function getFiltersDates($data, $query = null)
    {
        if ($query === null) $query = static::query();

        if (isset($data['created_at'])) static::filterDates($query, 'created_at', $data['created_at']);
        if (isset($data['updated_at'])) static::filterDates($query, 'updated_at', $data['updated_at']);

        if (isset($data['created_at'])) {
            $filter = $data['created_at'];


        }

        if (isset($filter['less'])) {
            if (!is_numeric($filter['less']) || $filter['less'] < 1) throw new ValidationException(['format' => ['created_at.less' => 'timestamp']]);
            //$time = new MongoDate($filter['less']);

            //$params['conditions']['created_at']['$lte'] = $time;
        }
        if (isset($filter['more'])) {
            if (!is_numeric($filter['more']) || $filter['more'] < 1) throw new ValidationException(['format' => ['created_at.less' => 'timestamp']]);
            $time = new MongoDate($filter['more']);

            $params['conditions']['created_at']['$gte'] = $time;
        }

        return $query;
    }

    /**
     * @param \Phalcon\Mvc\Model\Criteria $query
     * @param string $name
     * @param string $value
     */
    protected static function filterLike(&$query, $name, $value)
    {
        $query->andWhere($name . ' LIKE   :' . $name . ':')->bind([$name => '%' . $value . '%'], true);
    }

    /**
     * @param \Phalcon\Mvc\Model\Criteria $query
     * @param string $name
     * @param string $value
     * @param int|null $min
     * @param int|null $max
     * @throws ValidationException;
     */
    protected static function filterInterval(&$query, $name, $value, $min = null, $max = null)
    {
        $values = explode('-', $value);

        $rule = ['integer'];
        if ($min !== null) $rule[] = 'min:' . $min;
        if ($max !== null) $rule[] = 'max:' . $max;

        $rule = implode('|', $rule);

        $rules = [
            'min' => $rule,
            'max' => $rule,
            'value' => $rule
        ];

        $data = [];

        if (count($values) == 2) {
            if (!strlen($values[0])) $data['min'] = $values[0];
            if (!strlen($values[1])) $data['max'] = $values[1];

            if (!self::validateData($rules, $data)) throw new ValidationException(self::$validationMessages);

            if (isset($data['min'])) $query->andWhere($name . ' >= :' . $name . '_min:')->bind([$name . '_min' => $values[1]], true);
            if (isset($data['max'])) $query->andWhere($name . ' <= :' . $name . '_max:')->bind([$name . '_max' => $values[1]], true);
            
        } else {
            $data['value'] = $value;
            if (!self::validateData($rules, $data)) throw new ValidationException(self::$validationMessages);

            $query->andWhere($name . ' = :' . $name . ':')->bind([$name => $data['value']], true);
        }
    }

    /**
     * @param \Phalcon\Mvc\Model\Criteria $query
     * @param string $name
     * @param string $value
     * @param array|null $list
     * @throws ValidationException
     */
    protected static function filterValue(&$query, $name, $value, $list = null)
    {
        if (!in_array($value, $list)) throw new ValidationException(['required' => [$name => 'in']]);

        $query->andWhere($name . ' = :' . $name . ':')->bind([$name => $value], true);
    }

    /**
     * @param \Phalcon\Mvc\Model\Criteria $query
     * @param string $name
     * @param string $value
     * @throws ValidationException
     */
    protected static function filterDates(&$query, $name, $value)
    {
        if (isset($value['less'])) {
            if (!is_numeric($value['less']) || $value['less'] < 1) throw new ValidationException(['format' => [$name . '.less' => 'timestamp']]);
            $time =
            $query->andWhere($name . ' <= :' . $name . '_less:')->bind([$name . '_less' => $value['less']], true);
        }
        if (isset($value['more'])) {
            if (!is_numeric($value['more']) || $value['more'] < 1) throw new ValidationException(['format' => [$name . '.more' => 'timestamp']]);
            $query->andWhere($name . ' >= :' . $name . '_more:')->bind([$name . '_more' => $value['more']], true);
        }
    }
    
}