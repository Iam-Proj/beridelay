<?php namespace BeriDelay\Models;

use System\Models\Model;
use System\Traits\SoftDelete;
use System\Traits\Filters;

/**
 * Модель "Договор-оферта"
 * @package BeriDelay\Models
 * @method static Contract findFirstById(integer $id)
 */
class Contract extends Model
{
    use SoftDelete;
    use Filters;

    public $table = 'contract';

    /**
     * @var string Текст договора
     */
    public $text;

    /**
     * @var string Тип договора
     */
    public $type;
    
    public $behaviors = [
        'System\Behaviors\Loggable'
    ];

    public $validation = [
        'text' => 'required',
        'type' => 'in:public,private',
    ];

    public static $fields = ['id', 'text', 'type'];

    /**
     * @param array $data
     * @param \Phalcon\Mvc\Model\Criteria $query
     * @return \Phalcon\Mvc\Model\Criteria
     */
    public static function getFiltersBase($data, $query)
    {
        if (isset($data['type'])) self::filterValue($query, 'type', $data['type'], ['public', 'private']);

        return $query;
    }
}