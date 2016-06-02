<?php namespace BeriDelay\Models;

use System\Models\Model;
use System\Traits\SoftDelete;

/**
 * Модель "Контент"
 * @package BeriDelay\Models
 */
class Content extends Model
{
    use SoftDelete;

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

    public $validation = [
        'user_id' => 'required|integer',
        'history_id' => 'required|integer',
        'is_hide' => 'in:0,1',
        'content_type' => 'in:0,1'
    ];
}