<?php namespace BeriDelay\Models;

use System\Models\Model;
use System\Traits\SoftDelete;

/**
 * Модель "Цель"
 * @package BeriDelay\Models
 */
class Target extends Model
{
    use SoftDelete;

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

    public $validation = [
        'category_id' => 'required|integer',
        'name' => 'required|between:3,50',
        'is_hide' => 'in:0,1',
        'content_count' => 'integer',
        'start_count' => 'integer',
        'finish_count' => 'integer'
    ];
}