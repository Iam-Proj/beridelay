<?php namespace BeriDelay\Controllers;

use BeriDelay\Models\User;
use BeriDelay\Models\Invite;
use BeriDelay\Models\Token;
use BeriDelay\Models\Session;

class UsersController extends ApiBaseController
{
    public $actions = [
        'registration' => [
            'fields' => [
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
            ]
        ]
    ];

    public function registrationAction()
    {
        //проверяем, нет ли такого email или телефона
        $user = User::findExists($this->parameters['email'], $this->parameters['phone']);
        if ($user) {
            if ($user->email == $this->parameters['email']) return $this->error(self::ERROR_EMAIL_EXISTS, 'registration');
            if ($user->phone == $this->parameters['phone']) return $this->error(self::ERROR_PHONE_EXISTS, 'registration');
        }

        //если человек зашел по приглашению
        $invite = null;
        if ($this->parameters['invite']) {
            $invite = Invite::findByValue($this->parameters['invite']);
            if (!$invite || $invite->user_id != null) return $this->error(self::ERROR_INVITE_NOT_FOUND, 'registration');
        }

        //создаем пользователя
        $user = new User();

        $user->email = $this->parameters['email'];
        $user->password = $this->parametrs['password'];
        $user->name = $this->parameters['name'];
        $user->surname = $this->parameters['surname'];
        $user->patronim = $this->parameters['patronim'];
        $user->phone = $this->parameters['phone'];
        $user->age = $this->parameters['age'];
        $user->gender = $this->parameters['gender'];
        $user->city = $this->parameters['city'];
        $user->salary = $this->parameters['salary'];

        $user->save();

        //добаляем в приглашение информацию о том, что пользователь зарегистрировался
        if ($invite) {
            $invite->user_id = $user->id;
            $invite->save();
        }

        //лог
        $user->addLogEvent('registration');

        //создаем новый токен
        $token = new Token();
        $token->user_id = $user->id;
        $token->type = 'access';
        $token->save();

        //создаем сессию
        $session = new Session();
        $session->user_id = $user->id;
        $session->save();

        //логируем событие
        $user->addLogEvent('auth');

        return ['token_access' => $token->value];
    }
}

