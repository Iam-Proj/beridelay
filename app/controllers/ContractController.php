<?php namespace BeriDelay\Controllers;

use BeriDelay\Exceptions\ApiException;
use BeriDelay\Models\Contract;
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
            
            return Contract::get($this->request->getPost());

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

            if (!$token->user->is_admin) throw new ApiException(ApiException::FORBIDDEN);
            
            $data = $this->request->getPost();

            if (!isset($data['id'])) throw new ValidationException(['required' => ['id' => 'Required']]);
            if (!isset($data['text'])) throw new ValidationException(['required' => ['text' => 'Required']]);
            if (!is_numeric($data['id']) || $data['id'] < 1) throw new ValidationException(['format' => ['id' => 'integer']]);

            $contract = Contract::findFirstById($data['id']);
            $contract->text = $data['text'];
            $contract->save();

            return ['success' => true];
        } catch (BaseException $e) {
            $this->errorException($e);
        }
    }
}

