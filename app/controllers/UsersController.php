<?php namespace BeriDelay\Controllers;

use BeriDelay\Exceptions\ApiException;
use BeriDelay\Models\User;
use BeriDelay\Models\Token;
use System\Exceptions\BaseException;

class UsersController extends ApiBaseController
{
    /**
     * Регистрация пользователей
     */
    public function registrationAction()
    {
        try {

            $user = User::registration($this->request->getPost());
            $token = Token::add($user->id);

            //TODO: Сгенерировать новое задание и передать его в ответе

        } catch (BaseException $e) {
            return $this->errorException($e);
        }

        return ['token_access' => $token->value];
    }

    /**
     * Получает список пользователей
     */
    public function getAction()
    {
        try {
            //приватный метод
            $token = $this->hasPrivate();

            $filters = User::getFilters($this->request->getPost());
            
            //если фильтр заполнен
            if (count($filters)) {
                if (!$token->user->is_admin) throw new ApiException(ApiException::PARAM_ACCESS);
                return ['result' => User::get($filters)];
            } else {
                $user = $token->user->toArray(User::$fields);
                return ['result' => [$user]];
            }

        } catch (BaseException $e) {
            return $this->errorException($e);
        }
    }

    public function createAction()
    {

    }

    public function editAction()
    {

    }

    public function deleteAction()
    {
        return $this->delete('BeriDelay\Models\User');
    }
}

