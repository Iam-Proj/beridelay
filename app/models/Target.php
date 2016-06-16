<?php namespace BeriDelay\Models;

use System\Models\Model;
use System\Traits\SoftDelete;
use BeriDelay\Models\Tag;
use BeriDelay\Models\Tag2Target;
use System\Traits\Filters;

/**
 * Модель "Цель"
 * @package BeriDelay\Models
 */
class Target extends Model
{
    use SoftDelete;
    use Filters;
    
    /**
     * @var int ID категории
     */
    public $category_id;

    /**
     * @var string Название цели
     */
    public $name;

    /**
     * @var string Описание цели
     */
    public $description;

    /**
     * @var boolean Скрыта цель или нет
     */
    public $is_hide;

    /**
     * @var integer Минимальное количество контента для цели
     */
    public $content_count;

    /**
     * @var int Количество "взятия" цели
     */
    public $start_count;

    /**
     * @var int Количество завершений цели
     */
    public $finish_count;

    public $behaviors = [
        'System\Behaviors\Loggable'
    ];
    
    /**
     * @var int Количество целей в задании (по умолчанию)
     */
    public static $countTargetUser = 3;
    
    protected $hasManyToMany = [
        'Tags' => [
            'BeriDelay\Models\Tag',
            'model' => 'BeriDelay\Models\Tag2Target'
        ]
    ];
    
    public $validation = [
        'category_id' => 'required|integer',
        'name' => 'required|between:3,50',
        'is_hide' => 'in:0,1',
        'content_count' => 'integer',
        'start_count' => 'integer',
        'finish_count' => 'integer'
    ];
    
    public static function getFiltered($query,$data) { 
        if (isset($data['name']) && !empty($data['name'])) self::filterLike($query, 'name', $data['name']);
        if (isset($data['description']) && !empty($data['description'])) self::filterLike($query, 'description', $data['description']);
        if (isset($data['finish_count']) && !empty($data['finish_count'])) self::filterInterval($query, 'finish_count', $data['finish_count']);
        return $query;
    }
    
    /**
     * 
     * @param int $categoryId - ID категории
     */
    public static function countTargetsByCategory($categoryId){
        return self::count('category_id = '.$categoryId);
        
        
    }
    
}