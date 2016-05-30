<?php namespace BeriDelay\Models;

use System\Models\Collection;
/**
 * Model Session
 * @package BeriDelay\Models
 * @property User $user
 */
class Session extends Collection
{

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $created_at;

    /**
     *
     * @var integer
     */
    public $user_id;

    /**
     *
     * @var string
     */
    public $ip;

    public $belongsTo = [
        'user' => ['BeriDelay\Models\User']
    ];

}
