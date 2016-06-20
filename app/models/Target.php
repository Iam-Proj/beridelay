<?php namespace BeriDelay\Models;

use System\Models\Model;
use System\Traits\SoftDelete;
use System\Traits\Filters;
use Phalcon\Db\Column;

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

    public static $types = [
        'id' => Column::TYPE_INTEGER,
        'category_id' => Column::TYPE_INTEGER,
        'name' => Column::TYPE_VARCHAR,
        'description' => Column::TYPE_TEXT,
        'is_hide' => Column::TYPE_INTEGER,
        'salary' => Column::TYPE_INTEGER,
        'content_count' => Column::TYPE_INTEGER,
        'start_count' => Column::TYPE_INTEGER,
        'finish_count' => Column::TYPE_INTEGER,
        'created_at' => Column::TYPE_TIMESTAMP,
        'updated_at' => Column::TYPE_TIMESTAMP,
        'deleted_at' => Column::TYPE_TIMESTAMP,
    ];

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

    public static $fields = ['id', 'name', 'description', 'category_id', 'salary', 'start_count', 'finish_count', 'tags', 'is_hide'];
    
    public static function getFiltered($query,$data)
    {
        if (isset($data['name']) && !empty($data['name'])) self::filterLike($query, 'name', $data['name']);
        if (isset($data['description']) && !empty($data['description'])) self::filterLike($query, 'description', $data['description']);
        if (isset($data['finish_count']) && !empty($data['finish_count'])) self::filterInterval($query, 'finish_count', $data['finish_count']);
        return $query;
    }
    
    /**
     * @param int $categoryId ID категории
     * @return integer
     */
    public static function countTargetsByCategory($categoryId)
    {
        return self::count('category_id = '.$categoryId);
    }

    public function toArray($columns = null)
    {
        if ($columns == null && !empty(static::$fields)) $columns = static::$fields;

        $result = parent::toArray($columns);
        if ($columns != null && in_array('tags', $columns) && $this->tags) {
            $result['tags'] = [];
            $tags = $this->tags;
            /** @var Tag $tag */
            foreach ($tags as $tag) $result['tags'][] = $tag->toArray();
        }

        return $result;
    }
    
}