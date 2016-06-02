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

    public $dates = ['finished_at'];

    public $validation = [
        'user_id' => 'required|integer',
        'status' => 'in:0,1,2,10,20,30',
    ];
}