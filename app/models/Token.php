<?php namespace BeriDelay\Models;

use Carbon\Carbon;
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

    public $updated_at;

    //Связи
    public $belongsTo = [
        'user' => ['BeriDelay\Models\User']
    ];

    public $dynamicUpdate = false;

    public function beforeCreate()
    {
        $this->value = md5(microtime() . $this->user_id);
    }

    public static function getByUser($user_id, $type = 'access')
    {
        return self::findFirst([
            'conditions' => 'user_id = :user_id: and type = :type:',
            'bind' => ['user_id' => $user_id, 'type' => $type]
        ]);
    }

    public static function getByToken($token, $type = 'access')
    {
        return self::findFirst([
            'conditions' => 'value = :value: and type = :type:',
            'bind' => ['value' => $token, 'type' => $type]
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

    public function life()
    {
        $this->updated_at = null;
        $this->save();
    }

    public static function add($user_id, $type = 'access')
    {
        //очищаем старые токены пользователя
        static::clearTokens($user_id);

        //создаем новый токен
        $token = new static();
        $token->user_id = $user_id;
        $token->type = $type;
        $token->save();

        return $token;
    }

}
