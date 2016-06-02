<?php namespace BeriDelay\Models;

use System\Models\Model;

/**
 * Модель связи тега с целью
 * @package BeriDelay\Models
 */
class Tag2Target extends Model
{
    public $table = 'tag2target';

    /**
     * @var int ID тега
     */
    public $tag_id;

    /**
     * @var int ID цели
     */
    public $target_id;
}