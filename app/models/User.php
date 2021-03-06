<?php namespace BeriDelay\Models;

use BeriDelay\Exceptions\UserException;
use System\Exceptions\ValidationException;
use System\Helpers\Captcha;
use System\Models\Model;
use System\Traits\SoftDelete;
use System\Traits\Filters;
use Carbon\Carbon;

/**
 * Модель "Пользователь"
 * @package BeriDelay\Models
 * @property User $referral
 * @method static User findFirstByEmail(string $email)
 * @method static User findFirstById(integer $id)
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
     * @var integer ID приглоасившего пользователя
     */
    public $referral_id;

    /**
     * @var boolean Является ли пользователь администратором
     */
    public $is_admin;

    /**
     * @var boolean Является ли пользователь активированным
     */
    public $is_activate;

    private $password_original;
    public $validationExceptions = true;

    public $behaviors = [
        'System\Behaviors\Loggable'
    ];

    public $belongsTo = [
        'referral' => [
            'BeriDelay\Models\User',
            'key' => 'referral_id'
        ]
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
        'is_activate' => 'in:0,1',
        'referral_id' => 'integer'
    ];

    /**
     * @var array Поля для вывода
     */
    public static $fields = ['id', 'name', 'surname', 'patronim', 'email', 'phone', 'age', 'gender', 'city', 'salary', 'is_activate', 'is_admin', 'referral', 'task'];

    public function beforeCreate()
    {
        //проверяем, нет ли такого email или телефона
        $user = User::findFirst([
            'conditions' => 'phone = :phone: or email = :email:',
            'bind' => ['phone' => $this->phone, 'email' => $this->email]
        ]);

        if ($user) {
            if ($user->email == $this->email) throw new UserException(UserException::EMAIL_EXISTS);
            if ($user->phone == $this->phone) throw new UserException(UserException::PHONE_EXISTS);
        }

        $this->created_at = Carbon::now()->toDateTimeString();
    }

    public function beforeUpdate()
    {
        //проверяем, нет ли такого email или телефона
        $user = User::findFirst([
            'conditions' => '(phone = :phone: or email = :email:) and id <> :id:',
            'bind' => ['phone' => $this->phone, 'email' => $this->email, 'id' => $this->id]
        ]);

        if ($user) {
            if ($user->email == $this->email) throw new UserException(UserException::EMAIL_EXISTS);
            if ($user->phone == $this->phone) throw new UserException(UserException::PHONE_EXISTS);
        }
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
        $created_at = ($this->created_at instanceof Carbon) ? $this->created_at->toDateTimeString() : $this->created_at;
        return sha1(sha1(sha1($password) . $this->id) . $created_at);
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
            'password' => 'required',
            'name' => 'required|alpha|between:2,50',
            'surname' => 'required|alpha|between:2,50',
            'patronim' => 'required|alpha|between:2,50',
            'phone' => 'required|between:6,15',
            'age' => 'required|integer|between:1,99',
            'gender' => 'required|in:0,1',
            'city' => 'required',
            'salary' => 'required|in:1,2,3',
            'invite' => 'alpha_num',
            'referral_id' => 'integer',
            Captcha::$fieldName => 'required|captcha'
        ];
        
        if (getenv('ENVIRONMENT') == 'dev') unset($rules[Captcha::$fieldName]);

        if (!self::validateData($rules, $data)) throw new ValidationException(self::$validationMessages);

        //создаем пользователя
        $user = new User();

        $user->email = $data['email'];
        $user->password = $data['password'];
        $user->password_original = $data['password'];
        $user->name = $data['name'];
        $user->surname = $data['surname'];
        $user->patronim = $data['patronim'];
        $user->phone = $data['phone'];
        $user->age = $data['age'];
        $user->gender = $data['gender'];
        $user->city = $data['city'];
        $user->salary = $data['salary'];
        $user->is_admin = 0;
        $user->is_activate = 0;
        $user->referral_id = isset($data['referral_id']) ? $data['referral_id'] : 0;

        $user->save();

        //если человек зашел по приглашению
        $invite = null;
        if (isset($data['invite'])) {
            $invite = Invite::findByValue($data['invite']);
            if (!$invite || $invite->user_id != null) throw new UserException(UserException::INVITE_NOT_FOUND);
        }

        //добаляем в приглашение информацию о том, что пользователь зарегистрировался
        if ($invite) {
            $invite->user_id = $user->id;
            if (false == $invite->save()) throw new UserException(UserException::INTERNAL, ['errors' => $invite->getMessagesArray()]);
        }

        return $user->auth();
    }

    /**
     * @return $this
     */
    public function auth()
    {
        $this->addLogEvent('auth');

        //получаем текущую сессию
        $session = Session::findLastAuth();
        if (!$session) $session = new Session();
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
        $this->addLogEvent('signout');
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

        if (isset($data['referral_id'])) self::filterValue($query, 'referral_id', $data['referral_id']);
        if (isset($data['gender'])) self::filterValue($query, 'gender', $data['gender'], [0, 1]);
        if (isset($data['is_admin'])) self::filterValue($query, 'is_admin', $data['is_admin'], [0, 1]);
        if (isset($data['is_activate'])) self::filterValue($query, 'is_activate', $data['is_activate'], [0, 1]);
        if (isset($data['salary'])) self::filterValue($query, 'salary', $data['salary'], [0, 1, 2]);

        return $query;
    }

    public function toArray($columns = null)
    {
        if ($columns == null && !empty(static::$fields)) $columns = static::$fields;

        $result = parent::toArray($columns);
        if ($columns != null && in_array('referral', $columns) && $this->referral) $result['referral'] = $this->referral->toArray(['id', 'name', 'surname', 'patronim']);
        if ($columns != null && in_array('task', $columns)) {
            $task = Task::findFirst([
                'conditions' => 'user_id = :user_id: and status > 0',
                'bind' => ['user_id' => $this->id],
                'order' => 'status asc',
            ]);
            if ($task) $result['task'] = $task->toArray();
        }

        return $result;
    }

}
