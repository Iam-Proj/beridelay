<?php namespace BeriDelay\Models;

use System\Models\Model;
use Phalcon\Validation;

/**
 * Модель "Токен"
 * @package BeriDelay\Models
 * @property User $user
 */
class Token extends Model
{
    /**
     * @var string Тип токена
     */
    public $type;

    /**
     * @var string Значение
     */
    public $value;

    /**
     * @var integer Пользователь
     */
    public $user_id;

    //Связи
    public $belongsTo = [
        'user' => ['BeriDelay\Models\User']
    ];

    public function beforeCreate()
    {
        $this->value = md5(microtime() . $this->user_id);
    }

    public static function getToken($user_id, $type = 'access')
    {
        return self::findFirst([
            'conditions' => 'user_id = :user_id: and type = :type:',
            'bind' => ['user_id' => $user_id, 'type' => $type]
        ]);
    }

    public static function clearTokens($user_id, $type = 'access')
    {
        $tokens = self::find([
            'conditions' => 'user_id = :user_id: and type = :type:',
            'bind' => ['user_id' => $user_id, 'type' => $type]
        ]);
        foreach ($tokens as $token) $token->delete();
    }

}
