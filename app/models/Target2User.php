<?php namespace BeriDelay\Models;

use System\Models\Model;
use Phalcon\Db\Column;

/**
 * Модель связи цели с пользователем
 * @package BeriDelay\Models
 */
class Target2User extends Model
{
    public $table = 'target2user';

    /**
     * @var int ID цели
     */
    public $target_id;

    /**
     * @var int ID пользователя
     */
    public $user_id;

    public static $types = [
        'user_id' => Column::TYPE_INTEGER,
        'target_id' => Column::TYPE_INTEGER
    ];
}