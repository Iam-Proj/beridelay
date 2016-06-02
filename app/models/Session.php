<?php namespace BeriDelay\Models;

use System\Models\Collection;
use Carbon\Carbon;
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
     * @var Carbon
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

    public function beforeCreate()
    {
        $this->created_at = Carbon::now();
        $this->ip = isset($_SERVER['HTTP_X_REAL_IP']) && strlen($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['REMOTE_ADDR'];
    }

    public function beforeSave()
    {
        $this->created_at = $this->created_at->toDateTimeString();
    }

    public function afterFetch()
    {
        $this->created_at = new Carbon($this->created_at);
    }



}
