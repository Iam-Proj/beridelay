<?php namespace BeriDelay\Models;

use System\Models\Model;
use System\Traits\SoftDelete;
use System\Models\Photo;

/**
 * Модель "История"
 * @package BeriDelay\Models
 * @method static History findFirstById(integer $id)
 * @method boolean attachImage(File $object)
 */
class History extends Model
{
    use SoftDelete;

    /**
     * @var int ID пользователя
     */
    public $user_id;

    /**
     * @var int ID задания
     */
    public $task_id;

    /**
     * @var int ID цели
     */
    public $target_id;

    /**
     * @var string Описание цели от пользователя
     */
    public $description;

    /**
     * @var string Комментарий администратора
     */
    public $comment;

    public $attachOne = [
        'image' => ['System\Models\Photo'],
    ];

    public static $fields = ['id','user_id','task_id','target_id','description'];

    public $behaviors = [
        'System\Behaviors\Loggable'
    ];

    public $validation = [
        'user_id' => 'required|integer',
        'task_id' => 'required|integer',
        'target_id' => 'required|integer',
        'status' => 'in:0,1,2,3'
    ];
}