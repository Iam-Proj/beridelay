<?php namespace BeriDelay\Models;

use System\Models\Model;
use System\Traits\SoftDelete;
use System\Traits\Filters;
use System\Models\File;
use Phalcon\Mvc\Model\Criteria;

/**
 * Модель "Контент"
 * @package BeriDelay\Models
 * @property \System\Models\Photo $image
 * @property \System\Models\Video $video
 * @method boolean attachImage(File $object)
 * @method boolean attachVideo(File $object)
 */
class Content extends Model
{
    use SoftDelete;
    use Filters;

    /**
     * @var int ID пользователя
     */
    public $user_id;

    /**
     * @var int ID истории
     */
    public $history_id;

    /**
     * @var string Описание контента
     */
    public $description;

    /**
     * @var boolean Скрыт контент или нет
     */
    public $is_hide;

    /**
     * @var integer Тип контента
     */
    public $content_type;

    public $attachOne = [
        'image' => ['System\Models\Photo'],
        'video' => ['System\Models\Video']
    ];

    public $validation = [
        'user_id' => 'required|integer',
        'history_id' => 'required|integer',
        'is_hide' => 'in:0,1',
        'content_type' => 'in:0,1'
    ];

    public static $fields = ['id', 'user_id', 'history_id', 'description', 'is_hide', 'content_type'];

    /**
     * @param array $data
     * @param Criteria $query
     * @return Criteria
     */
    public static function getFiltersBase($data, $query)
    {
        if (isset($data['description'])) self::filterLike($query, 'description', $data['description']);

        if (isset($data['user_id'])) self::filterValue($query, 'user_id', $data['user_id']);
        if (isset($data['history_id'])) self::filterValue($query, 'history_id', $data['history_id']);
        if (isset($data['target_id'])) self::filterValue($query, 'target_id', $data['target_id']);

        return $query;
    }

}