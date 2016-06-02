<?php namespace BeriDelay\Models;

use System\Models\Model;

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

    /**
     * @var bool Цель уже отображалась пользователю
     */
    public $showed;

    /**
     * @var bool Цель уже выполнялась пользователем
     */
    public $started;
}