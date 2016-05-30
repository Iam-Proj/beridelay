<?php namespace BeriDelay\Models;

use System\Models\Model;

/**
 * Контактная информация пользователя
 * @package BeriDelay\Models
 */
class Contact extends Model
{
    public $table = 'user_contact';

    /**
     * @var integer Пользователь
     */
    public $user_id;

    /**
     * @var string Тип контакта
     */
    public $type;

    /**
     * @var string Значение
     */
    public $value;

}
