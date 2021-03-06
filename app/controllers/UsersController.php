<?php namespace BeriDelay\Controllers;

use BeriDelay\Exceptions\ApiException;
use BeriDelay\Exceptions\UserException;
use BeriDelay\Models\User;
use BeriDelay\Models\Task;
use BeriDelay\Models\Token;
use BeriDelay\Models\Invite;
use System\Exceptions\ValidationException;
use System\Exceptions\BaseException;

class UsersController extends ApiBaseController
{
    /**
     * Регистрация пользователей
     * @api
     */
    public function registrationAction()
    {
        try {

            $user = User::registration($this->request->getPost());
            $token = Token::add($user->id);

            //генерируем новое задание для пользователя
            $task = new Task();
            $task->user_id = $user->id;
            $task->save();

            $targets = $task->generate($user->salary);

        } catch (BaseException $e) {
            return $this->errorException($e);
        }

        return [
            'response' => [
                'token_access' => $token->value,
                'task' => [
                    'id' => $task->id,
                    'targets' => $targets
                ]
            ]
        ];
    }

    /**
     * Получает список пользователей
     * @api
     */
    public function getAction()
    {
        try {
            //приватный метод
            $token = $this->hasPrivate();

            $data = $this->request->getPost();

            if (!$token->user->is_admin) {
                $data = [];
                $data['id'] = $token->user_id;
                if (isset($data['referral_id']) && $data['referral_id'] == $token->user_id)
                    $data['referral_id'] = $token->user_id;
                else
                    $data['id'] = $token->user_id;

                $data['fields'] = ['id', 'name', 'surname', 'patronim', 'email', 'phone', 'age', 'gender', 'city', 'salary', 'referral'];

                return User::get($data);
            }

            return User::get($data);

        } catch (BaseException $e) {
            return $this->errorException($e);
        }
    }

    /**
     * Создает пользователя
     * @api
     */
    public function createAction()
    {
        try {
            $token = $this->hasPrivate();
            if (!$token->user->is_admin) throw new ApiException(ApiException::FORBIDDEN);

            //если человек зашел по приглашению
            $invite = null;
            if ($this->request->getPost('invite') != null) {
                $invite = Invite::findByValue($this->request->getPost('invite'));
                if (!$invite || $invite->user_id != null) throw new UserException(UserException::INVITE_NOT_FOUND);
            }

            $user = new User();
            $user->create($this->request->getPost(), [
                'name', 'surname', 'patronim', 'email', 'phone', 'age', 'gender', 'city', 'salary', 'invite', 'password', 'is_activate', 'is_admin', 'referral_id'
            ]);
            $user->refresh();

            //добаляем в приглашение информацию о том, что пользователь зарегистрировался
            if ($invite) {
                $invite->user_id = $user->id;
                if (false == $invite->save()) throw new UserException(UserException::INTERNAL, ['errors' => $invite->getMessagesArray()]);
            }


        } catch (BaseException $e) {
            return $this->errorException($e);
        }
        return ['response' => $user->toArray(User::$fields)];
    }

    /**
     * Редактирует пользователя
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

        } catch (BaseException $e) {
            $this->errorException($e);
        }

        return ['success' => true];
    }

    /**
     * Удаляет пользователя
     * @api
     */
    public function deleteAction()
    {
        return $this->delete('BeriDelay\Models\User');
    }
}

