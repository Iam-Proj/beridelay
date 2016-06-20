<?php namespace BeriDelay\Controllers;

use BeriDelay\Exceptions\UserException;
use BeriDelay\Models\User;
use BeriDelay\Models\Token;
use BeriDelay\Models\Session;
use System\Exceptions\ValidationException;
use System\Exceptions\BaseException;
use Carbon\Carbon;
use System\Helpers\Captcha;

class AuthController extends ApiBaseController
{
    /**
     * Осуществляет авторизацию пользователя в системе
     * @api
     */
    public function signinAction()
    {
        try {
            //получим запись по IP пользователя
            $session = Session::findLastAuth();

            //если сессия уже есть, то надо проверить дату последней пробы
            if ($session) {
                $time = Carbon::createFromTimestamp($session->created_at->sec);
                if ($time->addHour() > Carbon::now() and getenv('ENVIRONMENT') != 'dev') {
                    $rules = [Captcha::$fieldName => 'required|captcha'];
                    if (!User::validateData($rules, $this->request->getPost())) throw new ValidationException(User::$validationMessages);
                } else {
                    $session->created_at = new \MongoDate(time());
                    $session->save();
                }
            } else {
                $session = new Session();
                $session->user_id = 0;
                $session->save();
            }

            $user = User::signin($this->request->getPost('email'), $this->request->getPost('password'));
            $token = Token::add($user->id);
        } catch (BaseException $e) {
            return $this->errorException($e);
        }

        return ['response' => ['token_access' => $token->value]];
    }

    /**
     * Выход из сессии
     * @api
     */
    public function signoutAction()
    {
        try {
            $token = $this->hasPrivate();
            $user = $token->user;

            //удаляем текущий токен
            $token->delete();
            //выходим
            $user->signout();
        } catch (BaseException $e) {
            return $this->errorException($e);
        }

        return ['success' => true];
    }

    /**
     * Посылает письмо восстановления доступа к аккаунту. В письме содержится ключ для временного доступа к методу auth/restore. Время жизни ключа: 1 час
     * @api
     */
    public function restore_initAction()
    {
        try {
            $email = $this->request->getPost('email');
            if ($email == null) throw new ValidationException(['required' => ['email' => 'Required']]);

            if (!$user = User::findFirstByEmail($email)) throw new UserException(UserException::NOT_FOUND);

            $token = Token::add($user->id, Token::TOKEN_RESTORE);

            //TODO: посылать письмо с ссылкой
        } catch (BaseException $e) {
            return $this->errorException($e);
        }
        return ['success' => true];
    }

    /**
     * Восстановление пароля пользователя. Перед вызовом метода необходимо вызвать метод auth/restore_init
     * @api
     */
    public function restoreAction()
    {
        try {

            $key = $this->request->getPost('key');
            $password = $this->request->getPost('password');

            if ($key == null) throw new ValidationException(['required' => ['key' => 'Required']]);
            if ($password == null) throw new ValidationException(['required' => ['password' => 'Required']]);
            if (strlen($password) < 3) throw new ValidationException(['format' => ['password' => 'validation.min']]);

            if (!$token = Token::getByToken($key, Token::TOKEN_RESTORE)) throw new UserException(UserException::UNKNOWN_TOKEN);

            $user = $token->user;
            $token->delete();

            $user->password = $password;
            $user->save();

        } catch (BaseException $e) {
            return $this->errorException($e);
        }

        return ['success' => true];
    }

    /**
     * Активация аккаунта пользователя
     * @api
     */
    public function activationAction()
    {
        try {

            $key = $this->request->getPost('key');
            $password = $this->request->getPost('password');

            if ($key == null) throw new ValidationException(['required' => ['key' => 'Required']]);
            if ($password == null) throw new ValidationException(['required' => ['password' => 'Required']]);
            if (strlen($password) < 3) throw new ValidationException(['format' => ['password' => 'validation.min']]);

            if (!$token = Token::getByToken($key, Token::TOKEN_ACTIVATION)) throw new UserException(UserException::UNKNOWN_TOKEN);

            $user = $token->user;
            $token->delete();

            $user->is_activate = 1;
            $user->password = $password;
            $user->save();

        } catch (BaseException $e) {
            return $this->errorException($e);
        }

        return ['success' => true];
    }

    /**
     * Повторно посылает письмо с инструкциями по активации аккаунта пользователя
     * @api
     */
    public function send_activation_mailAction()
    {
        try {
            $email = $this->request->getPost('email');
            if ($email == null) throw new ValidationException(['required' => ['email' => 'Required']]);

            if (!$user = User::findFirstByEmail($email)) throw new UserException(UserException::NOT_FOUND);

            $token = Token::add($user->id, Token::TOKEN_ACTIVATION);

            //TODO: посылать письмо с ссылкой
        } catch (BaseException $e) {
            return $this->errorException($e);
        }

        return ['success' => true];
    }
}

