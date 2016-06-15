<?php namespace BeriDelay\Models;

use System\Models\Model;
use System\Traits\SoftDelete;
use Carbon\Carbon;

/**
 * Модель "Задание"
 * @package BeriDelay\Models
 */
class Task extends Model
{
    use SoftDelete;

    /**
     * @var int ID пользователя
     */
    public $user_id;

    /**
     * @var int Статус задания
     */
    public $status;

    /**
     * @var string Причина отклонения/блокировки
     */
    public $reason;

    /**
     * @var string Комментарий администратора
     */
    public $comment;

    /**
     * @var Carbon Время завершения задания
     */
    public $finished_at;

    /**
     *
     * @var int Все статусы 
     */
    public static $statuses = [
        0 => 'новое задание',
        1 => 'в работе',
        2 => 'на проверку',
        10 => 'завершено',
        20 => 'отклонено',
        30 => 'заблокировано',
    ];
    
    public $dates = ['finished_at'];

    protected $hasManyToMany = [
        'Targets' => [
            'BeriDelay\Models\Target',
            'model' => 'BeriDelay\Models\Task2Target'
        ]
    ];
    
    public $validation = [
        'user_id' => 'required|integer',
        'status' => 'in:0,1,2,10,20,30',
    ];
}