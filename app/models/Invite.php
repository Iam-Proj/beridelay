<?php namespace BeriDelay\Models;

use System\Models\Model;
use System\Traits\SoftDelete;
use System\Traits\Filters;

/**
 * Модель "Приглашение"
 * @package BeriDelay\Models
 * @method static Invite findByValue(string $value)
 */
class Invite extends Model
{
    use SoftDelete;
    use Filters;

    /**
     * @var string Код
     */
    public $value;

    /**
     * @var int ID пользователя
     */
    public $user_id;

    public $belongsTo = [
        'user' => ['BeriDelay\Models\User']
    ];

    public $validation = [
        'user_id' => 'integer'
    ];

    public static $fields = ['id', 'value', 'user_id'];

    public function beforeCreate()
    {
        $this->value = md5(microtime() . $this->user_id);
    }

    /**
     * @param array $data
     * @param \Phalcon\Mvc\Model\Criteria $query
     * @return \Phalcon\Mvc\Model\Criteria
     */
    public static function getFiltersBase($data, $query)
    {
        if (isset($data['value'])) self::filterValue($query, 'value', $data['value']);
        if (isset($data['user_id'])) self::filterValue($query, 'user_id', $data['user_id']);

        return $query;
    }
}