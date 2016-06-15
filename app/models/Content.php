<?php namespace BeriDelay\Models;

use System\Models\Model;
use System\Traits\SoftDelete;
use System\Traits\Filters;
use System\Models\File;

/**
 * Модель "Контент"
 * @package BeriDelay\Models
 * @method boolean attachFile(File $object)
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
        'file' => ['System\Models\File']
    ];

    public $validation = [
        'user_id' => 'required|integer',
        'history_id' => 'required|integer',
        'is_hide' => 'in:0,1',
        'content_type' => 'in:0,1'
    ];

    public static $fields = ['id', 'user_id', 'history_id', 'description', 'is_hide', 'content_type'];
}