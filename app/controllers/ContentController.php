<?php namespace BeriDelay\Controllers;

use BeriDelay\Exceptions\ApiException;
use BeriDelay\Models\Content;
use BeriDelay\Models\History;
use System\Exceptions\ValidationException;
use System\Exceptions\BaseException;
use System\Models\File;

class ContentController extends ApiBaseController
{

    /**
     * Получает список контента
     * @api
     */
    public function getAction()
    {
        try {
            
            return Content::get($this->request->getPost());

        } catch (BaseException $e) {
            return $this->errorException($e);
        }
    }

    /**
     * Создает контент
     * @api
     */
    public function createAction()
    {
        try {
            $token = $this->hasPrivate();

            $data = $this->request->getPost();

            $rules = [
                'history_id' => 'required|integer',
                'content_type' => 'required|in:0,1'
            ];

            if (!Content::validateData($rules, $data)) throw new ValidationException(Content::$validationMessages);

            //существует ли история
            $history = History::findFirstById($data['history_id']);
            if (!$history) throw new ApiException(ApiException::OBJECT_NOT_FOUND);

            if (!$token->user->is_admin && $history->user_id != $token->user_id) throw new ApiException(ApiException::OBJECT_ACCESS);

            if (!$this->request->hasFiles()) throw new ValidationException(['required' => ['photos' => 'Required', 'video' => 'Required']]);

            $result = [];

            foreach ($this->request->getUploadedFiles() as $upload) {
                $file = new File();
                $file->data = $upload;

                $content = new Content();
                $content->user_id = $token->user_id;
                $content->history_id = $history->id;
                $content->content_type = $data['content_type'];
                $content->save();
                
                $content->attachFile($file);

                $result = $content->toArray();
            }

            return ['result' => $result, 'count' => count($result), 'page' => 1, 'pageCount' => 1, 'offset' => 0];
        } catch (BaseException $e) {
            return $this->errorException($e);
        }
    }

    /**
     * Редактирует контент
     * @api
     */
    public function editAction()
    {
        try {
            $token = $this->hasPrivate();

            if (!$token->user->is_admin) throw new ApiException(ApiException::FORBIDDEN);

            //$invite = new Invite();
            //$invite->save();

            //return ['success' => true, 'value' => $invite->value];
        } catch (BaseException $e) {
            return $this->errorException($e);
        }
    }

    /**
     * Удаляет контент
     * @api
     */
    public function deleteAction()
    {
        return $this->delete('BeriDelay\Models\Content');
    }
}

