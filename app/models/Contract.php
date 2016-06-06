<?php namespace BeriDelay\Models;

use System\Models\Model;
use System\Traits\SoftDelete;

/**
 * Модель "Договор-оферта"
 * @package BeriDelay\Models
 */
class Contract extends Model
{
    use SoftDelete;

    /**
     * @var string Текст договора
     */
    public $text;

    /**
     * @var string Тип договора
     */
    public $type;

    public $validation = [
        'text' => 'required',
        'type' => 'in:public,private',
    ];
}