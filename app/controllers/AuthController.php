<?php namespace BeriDelay\Controllers;

use BeriDelay\Models\User;
use BeriDelay\Models\Token;
use BeriDelay\Models\Session;

class AuthController extends ApiBaseController
{
    public $actions = [
        'signin' => [
            'fields' => [
                'email' => 'required|email',
                'password' => 'required',
            ]
        ],
        'signout' => [
            'isPrivate' => true
        ],
        'restore_init' => [
            'fields' => [
                'email' => 'required|email',
            ]
        ],
        'restore' => [
            'fields' => [
                'key' => 'required',
                'password' => 'required|min:5',
            ]
        ],
        'activation' => [
            'fields' => [
                'key' => 'required',
                'password' => 'required|min:5',
            ]
        ],
        'send_activation_mail' => [
            'fields' => [
                'email' => 'required|email',
            ]
        ]
    ];

    /**
     * Осуществляет авторизацию пользователя в системе
     */
    public function signinAction()
    {
        $user = User::findFirstByEmail($this->parameters['email']);
        if (!$user) return $this->error(self::ERROR_ACCOUNT_NOT_FOUND, 'signin');
        if ($user->hashPassword($this->parameters['password']) != $user->password) return $this->error(self::ERROR_ACCOUNT_NOT_FOUND, 'signin '.$user->password.'  '.$user->hashPassword($this->parameters['password']) . ' ' .$user->created_at);

        //очищаем старые токены пользователя
        Token::clearTokens($user->id);

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

    /**
     * Выход из сессии
     */
    public function signoutAction()
    {
        //удаляем текущий токен
        $this->token->delete();

        //логируем событие
        $this->user->addLogEvent('signout');

        return ['success' => true];
    }

    /**
     * Посылает письмо восстановления доступа к аккаунту. В письме содержится ключ для временного доступа к методу auth/restore. Время жизни ключа: 1 час
     */
    public function restore_initAction()
    {

    }

    /**
     * Восстановление пароля пользователя. Перед вызовом метода необходимо вызвать метод auth/restore_init
     */
    public function restoreAction()
    {

    }

    /**
     * Активация аккаунта пользователя
     */
    public function activationAction()
    {

    }

    /**
     * Повторно посылает письмо с инструкциями по активации аккаунта пользователя
     */
    public function send_activation_mailAction()
    {

    }
}

