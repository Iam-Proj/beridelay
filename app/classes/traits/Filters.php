<?php namespace System\Traits;

use System\Exceptions\ValidationException;
/**
 * Трайт "Фильтры"
 * Позволяет фильтровать данные
 * @package System\Traits
 */
trait Filters
{
    //const FILTER_ID =
    protected $filterType = [];

    /**
     * @param \Phalcon\Mvc\Model\Criteria|array $data
     * @return array
     */
    public static function get($data)
    {
        $query = $data;
        if (!$data instanceof \Phalcon\Mvc\Model\Criteria) $query = static::getFilters($data);
        $columns = isset($data['fields']) ? array_intersect(static::$fields, $data['fields']) : static::$fields;
        $query->columns($columns);

        $sort_field = isset($data['sort']) ? $data['sort'] : 'created_at';
        $sort_order = isset($data['sort_direction']) ? $data['sort_direction'] == 1 ? 'desc' : 'asc' : 'asc';

        $query->orderBy($sort_field . ' ' . $sort_order);

        $query->limit(isset($data['count']) ? $data['count'] : 100, isset($data['offset']) ? $data['offset'] : null);

        return $query->execute()->toArray();
    }

    /**
     * @param array $data
     * @return \Phalcon\Mvc\Model\Criteria
     * @throws ValidationException
     */
    public static function getFilters($data)
    {
        return self::getFiltersBase($data);
    }

    /**
     * @param array $data
     * @param \Phalcon\Mvc\Model\Criteria  $query
     * @return \Phalcon\Mvc\Model\Criteria
     * @throws ValidationException
     */
    public static function getFiltersBase($data, $query = null)
    {
        if ($query === null) $query = static::query();

        if (isset($data['ids'])) {
            $ids = [];
            foreach ($data['ids'] as $id) if (is_numeric($id) && $id > 0) $ids[] = $id;
            if (!count($ids)) throw new ValidationException(['required' => [], 'format' => ['ids' => 'integer.array']]);

            $query->inWhere('id', $ids);
        } elseif (isset($data['id'])) {
            $query->where('id = :id:')->bind(['id' => $data['id']]);
        } else {
            if (isset($data['minute'])) {
                if (!is_numeric($data['minute']) || $data['minute'] < 0 || $data['minute'] > 59) throw new ValidationException(['required' => [], 'format' => ['minute' => 'minute']]);
            }
        }

        return $query;
    }
    
}