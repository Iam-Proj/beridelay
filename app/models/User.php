<?php namespace BeriDelay\Models;

use System\Models\Model;
use System\Traits\SoftDelete;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\InclusionIn;

/**
 * Модель "Пользователь"
 * @package BeriDelay\Models
 * @property Session|array $sessions
 */
class User extends Model
{
    use SoftDelete;
    /**
     * @var string Имя
     */
    public $name;

    /**
     * @var string Email
     */
    public $email;

    /**
     * @var string Пароль
     */
    protected $password;

    /**
     * @var boolean Является ли пользователь администратором
     */
    public $is_admin;

    private $password_original;

    //Связи
    public $hasMany = [
        'contacts' => ['BeriDelay\Models\Contact']
    ];

    public $attachMany = [
        'files' => ['System\Models\File']
    ];

    public $behaviors = [
        'System\Behaviors\Loggable'
    ];

    public function beforeUpdate()
    {
        if ($this->id && $this->password_original) {
            $this->password = $this->hashPassword($this->password_original);
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
        return sha1(sha1(sha1($password) . $this->id) . $this->created_at);
    }

    public function setPassword($password)
    {
        if (strlen($password) < 3) {
            throw new \InvalidArgumentException('Пароль слишком короткий');
        }
        $this->password_original = $password;
    }

    /**
     * Валидация
     * @return boolean
     */
    public function validation()
    {
        $validation = new Validation();
        $validation->add('name', new PresenceOf([
            'message' => 'Поле "Имя" обязательно'
        ]));
        $validation->add('name', new StringLength([
            'min' => 2,
            'max' => 50,
            'messageMinimum' => 'Слишком короткое имя',
            'messageMaximum' => 'Слишком длинное имя'

        ]));
        $validation->add('email', new PresenceOf([
            'message' => 'Поле "Email" обязательно'
        ]));
        $validation->add('email', new Email([
            'message' => 'Указан неверный email'
        ]));
        $validation->add('email', new Uniqueness([
            'field' => 'email',
            'message' => 'Такой email уже зарегистрирован',
            'model' => $this
        ]));
        $validation->add('is_admin', new InclusionIn([
            'message' => 'Поле "Администратор" может принимать значение "True" и "False"',
            'domain' => [0, 1]
        ]));

        return $this->validate($validation);
    }

    public function auth()
    {
        $this->addLogEvent('auth');
    }

}
