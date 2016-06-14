<?php namespace BeriDelay\Controllers;

use BeriDelay\Exceptions\ApiException;
use BeriDelay\Exceptions\UserException;
use BeriDelay\Models\Contract;
use BeriDelay\Models\Token;
use BeriDelay\Models\Invite;
use System\Exceptions\ValidationException;
use System\Exceptions\BaseException;

class InviteController extends ApiBaseController
{

    /**
     * Получает список приглашений
     * @api
     */
    public function getAction()
    {
        try {
            
            return Invite::get($this->request->getPost());

        } catch (BaseException $e) {
            return $this->errorException($e);
        }
    }

    /**
     * Создает приглашение
     * @api
     */
    public function createAction()
    {
        try {
            $token = $this->hasPrivate();

            if (!$token->user->is_admin) throw new ApiException(ApiException::FORBIDDEN);

            $invite = new Invite();
            $invite->save();

            return ['success' => true, 'value' => $invite->value];
        } catch (BaseException $e) {
            return $this->errorException($e);
        }
    }

    /**
     * Удаляет приглашение
     * @api
     */
    public function deleteAction()
    {
        return $this->delete('BeriDelay\Models\Invite');
    }
}

