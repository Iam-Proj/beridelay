<?php namespace BeriDelay\Models;

use System\Models\Collection;
use Carbon\Carbon;
use MongoRegex;
/**
 * Model Session
 * @package BeriDelay\Models
 * @property User $user
 */
class Session extends Collection
{
    /**
     * @var Carbon
     */
    public $created_at;

    /**
     * @var integer
     */
    public $user_id;

    /**
     * @var string
     */
    public $ip;

    protected $belongsTo = [
        'user' => ['BeriDelay\Models\User']
    ];

    public function beforeCreate()
    {
        $this->ip = isset($_SERVER['HTTP_X_REAL_IP']) && strlen($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['REMOTE_ADDR'];
        $this->created_at = new \MongoDate(time());
    }

    public static function getFilters($data, $params = [])
    {
        if (isset($data['ip'])) $params['conditions']['ip'] = new MongoRegex('/.*' . $data['ip'] . '.*/i');
        if (isset($data['user_id'])) $params['conditions']['user_id'] = (int) $data['user_id'];

        $params = parent::getFilters($data, $params);

        return $params;
    }

}
