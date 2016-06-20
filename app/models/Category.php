<?php namespace BeriDelay\Models;

use System\Models\Model;
use System\Traits\SoftDelete;
use Phalcon\Db\Column;

/**
 * Модель "Категория целей"
 * @package BeriDelay\Models
 */
class Category extends Model
{
    use SoftDelete;

    /**
     * @var integer ID родительской категории
     */
    public $category_id;

    /**
     * @var string Название категории
     */
    public $name;

    /**
     * @var boolean Скрыта категория или нет
     */
    public $is_hide;

    /**
     *
     * @var types типы полей 
     */
    public static $types = [
        'id'          => Column::TYPE_INTEGER,
        'category_id' => Column::TYPE_INTEGER,
        
];
    
    public $behaviors = [
        'System\Behaviors\Loggable'
    ];

    public $validation = [
        'category_id' => 'integer',
        'name' => 'between:2,50',
        'is_hide' => 'in:0,1'
    ];
    
    public static function countChilds($catId){
        return self::count('category_id = '.$catId);
    }
    
}