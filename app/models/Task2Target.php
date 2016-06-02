<?php namespace BeriDelay\Models;

use System\Models\Model;

/**
 * Модель связи задания с целью
 * @package BeriDelay\Models
 */
class Task2Target extends Model
{
    public $table = 'task2target';

    /**
     * @var int ID задания
     */
    public $task_id;

    /**
     * @var int ID цели
     */
    public $target_id;
}