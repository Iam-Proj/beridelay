<?php namespace BeriDelay\Controllers;

use BeriDelay\Exceptions\ApiException;
use System\Models\Log;
use System\Exceptions\ValidationException;
use System\Exceptions\BaseException;

class LogsController extends ApiBaseController
{
    public function getAction()
    {
        try {
            //приватный метод
            $token = $this->hasPrivate();
            if (!$token->user->is_admin) throw new ApiException(ApiException::FORBIDDEN);

            $data = $this->request->getPost();

            if (isset($data['ids'])) {
                if (!is_array($data['ids']) || !count($data['ids'])) throw new ValidationException(['format' => ['ids' => 'array.integer']]);
                $logs = Log::findByIds($data['ids']);
                $count = count($logs);
            } elseif(isset($data['id'])) {
                $logs = [Log::findById($data['id'])];
                $count = count($logs);
            } else {
                $logs = Log::findByFilters($data);
                $count = Log::countByFilters($data);
            }

        } catch (BaseException $e) {
            return $this->errorException($e);
        }

        return ['result' => $logs];
    }
}

