<?php namespace BeriDelay\Models;

use System\Models\Model;
use System\Traits\SoftDelete;

/**
 * Модель "Приглашение"
 * @package BeriDelay\Models
 * @method static Invite findByValue(string $value)
 */
class Invite extends Model
{
    use SoftDelete;

    /**
     * @var string Код
     */
    public $value;

    /**
     * @var int ID пользователя
     */
    public $user_id;

    public $validation = [
        'user_id' => 'integer'
    ];
}