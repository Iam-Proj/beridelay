<?php namespace System\Traits;

use System\Exceptions\ValidationException;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
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
        //$this->modelsManager->createBuilder()
        if ($query == null) $query = static::getFilters($data);

        $sort_field = isset($data['sort']) ? $data['sort'] : 'created_at';
        $sort_order = isset($data['sort_direction']) ? $data['sort_direction'] == 1 ? 'desc' : 'asc' : 'asc';

        $query->orderBy($sort_field . ' ' . $sort_order);

        $count = $query->columns('COUNT(*)')->execute();

        $columns = isset($data['fields']) ? array_intersect(static::$fields, $data['fields']) : static::$fields;
        $query->columns($columns);

        $query->limit(isset($data['count']) ? $data['count'] : 100, isset($data['offset']) ? $data['offset'] : null);

        $result = $query->execute();

        return ['result' => $result, 'count' => $count->toArray()[0]];

        /*$paginator = new PaginatorModel(
            array(
                "data"  => Products::find(),
                "limit" => 10,
                "page"  => $currentPage
            )
        );*/


        //return $result
    }

    public static function getCount($data, $query = null)
    {
        if ($query == null) $query = static::getFilters($data);
        return $query->columns('COUNT(*) AS count')->execute();
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

        if (isset($data['minute'])) {
            if (!is_numeric($data['minute']) || $data['minute'] < 0 || $data['minute'] > 59) throw new ValidationException(['required' => [], 'format' => ['minute' => 'minute']]);
        }

        return $query;
    }

    /**
     * @param \Phalcon\Mvc\Model\Criteria $query
     * @param string $name
     * @param string $value
     * @return \Phalcon\Mvc\Model\Criteria
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
     * @return \Phalcon\Mvc\Model\Criteria
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
     * @return \Phalcon\Mvc\Model\Criteria
     * @throws ValidationException
     */
    protected static function filterValue(&$query, $name, $value, $list = null)
    {
        if (!in_array($value, $list)) throw new ValidationException(['required' => [$name => 'in']]);

        $query->andWhere($name . ' = :' . $name . ':')->bind([$name => $value], true);
    }
    
}