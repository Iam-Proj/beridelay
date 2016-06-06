<?php namespace BeriDelay\Models;

use System\Exceptions\UserException;
use System\Models\Model;
use System\Traits\SoftDelete;
use Carbon\Carbon;

/**
 * Модель "Пользователь"
 * @package BeriDelay\Models
 * @property Session|array $sessions
 */
class User extends Model
{
    use SoftDelete;

    /**
     * @var string Email
     */
    public $email;

    /**
     * @var string Пароль
     */
    protected $password;

    /**
     * @var string Имя
     */
    public $name;

    /**
     * @var string Фамилия
     */
    public $surname;

    /**
     * @var string Отчество
     */
    public $patronim;

    /**
     * @var integer Номер телефона
     */
    protected $phone;

    /**
     * @var integer Возраст
     */
    public $age;

    /**
     * @var integer Пол
     */
    public $gender;

    /**
     * @var string Город
     */
    public $city;

    /**
     * @var integer Уровень зарплаты
     */
    public $salary;

    /**
     * @var boolean Является ли пользователь администратором
     */
    public $is_admin;

    /**
     * @var boolean Является ли пользователь активированным
     */
    public $is_activate;

    private $password_original;

    public $behaviors = [
        'System\Behaviors\Loggable'
    ];

    public $validation = [
        'email' => 'required|email',
        'name' => 'required|alpha|between:2,50',
        'surname' => 'required|alpha|between:2,50',
        'patronim' => 'required|alpha|between:2,50',
        'phone' => 'required|between:6,15',
        'age' => 'required|integer|between:1,99',
        'gender' => 'required|in:0,1',
        'city' => 'required',
        'salary' => 'required|in:1,2,3',
        'is_admin' => 'in:0,1',
        'is_activate' => 'in:0,1'
    ];

    public function beforeCreate()
    {
        $this->created_at = Carbon::now()->toDateTimeString();
    }

    public function afterCreate()
    {
        if ($this->id && $this->password_original) {
            $this->password = $this->hashPassword($this->password_original);
            $this->save();
        }
    }

    public function hashPassword($password)
    {
        return sha1(sha1(sha1($password) . $this->id) . $this->created_at);
    }

    public function setPassword($password)
    {
        if ($this->id) {
            $this->password = $this->hashPassword($password);
        } else {
            $this->password = $password;
            $this->password_original = $password;
        }
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPhone($value)
    {
        $phone = preg_replace('/[^0-9]/iu', '', $value);
        $this->phone = $phone;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public static function findExists($email, $phone)
    {
        return self::findFirst([
            'conditions' => 'phone = :phone: or email = :email:',
            'bind' => ['phone' => $phone, 'email' => $email]
        ]);
    }

    public static function registration($parameters)
    {

    }

    public static function signin($email, $password)
    {
        $user = static::findFirstByEmail($email);

        if (!$user) throw new UserException(UserException::NOT_FOUND);
        if ($user->hashPassword($password) != $user->password) throw new UserException(UserException::NOT_FOUND);

        $user->addLogEvent('auth');

        //создаем сессию
        $session = new Session();
        $session->user_id = $user->id;
        $session->save();

        return $user;
    }
    
    public function signout()
    {
        //логируем событие
        $this->user->addLogEvent('signout');
    }

}
