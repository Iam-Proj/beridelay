<?php namespace BeriDelay\Controllers;

use BeriDelay\Exceptions\ApiException;
use BeriDelay\Exceptions\UserException;
use BeriDelay\Models\Contract;
use BeriDelay\Models\Token;
use BeriDelay\Models\Invite;
use System\Exceptions\ValidationException;
use System\Exceptions\BaseException;

class ContractController extends ApiBaseController
{

    /**
     * Получает список договоров
     * @api
     */
    public function getAction()
    {
        try {

            Contract::find(['id = 1'])->delete();
            return ['result' => [Contract::get($this->request->getPost())]];

        } catch (BaseException $e) {
            return $this->errorException($e);
        }
    }

    /**
     * Редактирует договор
     * @api
     */
    public function editAction()
    {
        try {
            $token = $this->hasPrivate();
            
            $data = $this->request->getPost();

            if (isset($data['id']) || isset($data['password']) || isset($data['is_admin']) || isset($data['is_activate']) || isset($data['email']) || isset($data['salary']) || isset($data['phone'])) {
                // Только для администратора
                if (!$token->user->is_admin) throw new ApiException(ApiException::FORBIDDEN);

                if (!isset($data['id'])) throw new ValidationException(['required' => ['id' => 'Required']]);
                
                $user = User::findFirstById($data['id']);
                $user->update($data, ['name', 'surname', 'patronim', 'gender', 'city', 'age', 'salary', 'phone', 'email', 'password', 'is_admin', 'is_activate']);

            } else {
                // Пользователь редактирует свои данные
                $user = $token->user;

                if (isset($data['new_password'])) {
                    if (!isset($data['old_password'])) throw new ValidationException(['required' => ['old_password' => 'Required']]);
                    if ($user->hashPassword($data['old_password']) != $user->password) throw new UserException(UserException::INCORRECT_PASSWORD);
                    $user->password = $data['new_password'];
                }
                $user->update($data, ['name', 'surname', 'patronim', 'gender', 'city', 'age']);
            }

            return ['success' => true];
        } catch (BaseException $e) {
            $this->errorException($e);
        }
    }
}

