<?php namespace BeriDelay\Controllers;

use BeriDelay\Exceptions\ApiException;
use BeriDelay\Models\Session;
use System\Exceptions\ValidationException;
use System\Exceptions\BaseException;

class SessionsController extends ApiBaseController
{
    public function getAction()
    {
        try {
            //приватный метод
            $token = $this->hasPrivate();

            $data = $this->request->getPost();

            if (isset($data['ids'])) {
                if (!$token->user->is_admin) throw new ApiException(ApiException::FORBIDDEN);
                if (!is_array($data['ids']) || !count($data['ids'])) throw new ValidationException(['format' => ['ids' => 'array.integer']]);
                $logs = Session::findByIds($data['ids']);
                $count = count($logs);
            } elseif(isset($data['id'])) {
                if (!$token->user->is_admin) throw new ApiException(ApiException::FORBIDDEN);
                $logs = [Session::findById($data['id'])];
                $count = count($logs);
            } else {
                if (isset($data['user_id'])) {
                    if (!$token->user->is_admin) throw new ApiException(ApiException::FORBIDDEN);
                } else {
                    $data['count'] = 5;
                }

                $logs = Session::findByFilters($data);
                $count = Session::countByFilters($data);
            }

        } catch (BaseException $e) {
            return $this->errorException($e);
        }

        return ['result' => $logs, 'count' => $count];
    }
}

