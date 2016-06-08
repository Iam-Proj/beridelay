<?php namespace BeriDelay\Models;

use BeriDelay\Exceptions\UserException;
use System\Exceptions\ValidationException;
use System\Models\Model;
use System\Traits\SoftDelete;
use System\Traits\Filters;
use Carbon\Carbon;

/**
 * Модель "Пользователь"
 * @package BeriDelay\Models
 * @property Session|array $sessions
 * @method User findFirstByEmail(string $email)
 *
 */
class User extends Model
{
    use SoftDelete;
    use Filters;

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

    /**
     * @var array Поля для вывода
     */
    public static $fields = ['id', 'name', 'surname', 'patronim', 'email', 'phone', 'age', 'gender', 'city', 'salary'];

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

    /**
     * @param array $data
     * @return User
     * @throws UserException
     * @throws ValidationException
     */
    public static function registration($data)
    {
        $rules = [
            'email' => 'required|email',
            'name' => 'required|alpha|between:2,50',
            'surname' => 'required|alpha|between:2,50',
            'patronim' => 'required|alpha|between:2,50',
            'phone' => 'required|between:6,15',
            'age' => 'required|integer|between:1,99',
            'gender' => 'required|in:0,1',
            'city' => 'required',
            'salary' => 'required|in:1,2,3',
            'invite' => 'alpha_num',
            'password' => 'required|min:3'
        ];

        if (!self::validateData($rules, $data)) throw new ValidationException(self::$validationMessages);

        //проверяем, нет ли такого email или телефона
        $user = User::findFirst([
            'conditions' => 'phone = :phone: or email = :email:',
            'bind' => ['phone' => $data['phone'], 'email' => $data['email']]
        ]);

        if ($user) {
            if ($user->email == $data['email']) throw new UserException(UserException::EMAIL_EXISTS);
            if ($user->phone == $data['phone']) throw new UserException(UserException::PHONE_EXISTS);
        }

        //если человек зашел по приглашению
        $invite = null;
        if (isset($data['invite'])) {
            $invite = Invite::findByValue($data['invite']);
            if (!$invite || $invite->user_id != null) throw new UserException(UserException::INVITE_NOT_FOUND);
        }

        //создаем пользователя
        $user = new User();

        $user->email = $data['email'];
        $user->password = $data['password'];
        $user->name = $data['name'];
        $user->surname = $data['surname'];
        $user->patronim = $data['patronim'];
        $user->phone = $data['phone'];
        $user->age = $data['age'];
        $user->gender = $data['gender'];
        $user->city = $data['city'];
        $user->salary = $data['salary'];

        $user->save();

        if (false == $user->save()) throw new UserException(UserException::INTERNAL, ['errors' => $user->getMessagesArray()]);

        //добаляем в приглашение информацию о том, что пользователь зарегистрировался
        if ($invite) {
            $invite->user_id = $user->id;
            if (false == $invite->save()) throw new UserException(UserException::INTERNAL, ['errors' => $invite->getMessagesArray()]);
        }

        //лог
        $user->addLogEvent('registration');

        return $user->auth();
    }

    /**
     * @return $this
     */
    public function auth()
    {
        $this->addLogEvent('auth');

        //создаем сессию
        $session = new Session();
        $session->user_id = $this->id;
        $session->save();

        return $this;
    }

    /**
     * @param string $email
     * @param string $password
     * @return User
     * @throws UserException
     * @throws ValidationException
     */
    public static function signin($email, $password)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required|min:3'
        ];

        if (!self::validateData($rules, ['email' => $email, 'password' => $password])) throw new ValidationException(self::$validationMessages);

        $user = static::findFirstByEmail($email);

        if (!$user) throw new UserException(UserException::NOT_FOUND);
        if ($user->hashPassword($password) != $user->password) throw new UserException(UserException::NOT_FOUND);

        return $user->auth();
    }
    
    public function signout()
    {
        //логируем событие
        $this->user->addLogEvent('signout');
    }

    /**
     * @param array $data
     * @param \Phalcon\Mvc\Model\Criteria $query
     * @return \Phalcon\Mvc\Model\Criteria
     * @throws ValidationException
     */
    public static function getFiltersBase($data, $query)
    {
        if (isset($data['name'])) self::filterLike($query, 'name', $data['name']);
        if (isset($data['surname'])) self::filterLike($query, 'surname', $data['surname']);
        if (isset($data['patronim'])) self::filterLike($query, 'patronim', $data['patronim']);
        if (isset($data['email'])) self::filterLike($query, 'email', $data['email']);
        if (isset($data['city'])) self::filterLike($query, 'city', $data['city']);
        if (isset($data['phone'])) self::filterLike($query, 'phone', $data['phone']);
        
        if (isset($data['age'])) self::filterInterval($query, 'age', $data['age']);

        if (isset($data['gender'])) self::filterValue($query, 'gender', $data['gender'], [0, 1]);
        if (isset($data['is_admin'])) self::filterValue($query, 'is_admin', $data['is_admin'], [0, 1]);
        if (isset($data['is_activate'])) self::filterValue($query, 'is_activate', $data['is_activate'], [0, 1]);
        if (isset($data['salary'])) self::filterValue($query, 'salary', $data['salary'], [0, 1, 2]);

        return $query;
    }

}
