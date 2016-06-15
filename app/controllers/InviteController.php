<?php namespace BeriDelay\Controllers;

use BeriDelay\Exceptions\ApiException;
use BeriDelay\Models\Invite;
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

            return ['response' => $invite];
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

