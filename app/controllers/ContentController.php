<?php namespace BeriDelay\Controllers;

use BeriDelay\Exceptions\ApiException;
use BeriDelay\Models\Content;
use BeriDelay\Models\History;
use System\Exceptions\ValidationException;
use System\Exceptions\BaseException;
use System\Models\File;
use Phalcon\Mvc\Model\Resultset;

class ContentController extends ApiBaseController
{

    /**
     * Получает список контента
     * @api
     */
    public function getAction()
    {
        try {
            //приватный метод
            $token = $this->hasPrivate();

            $data = $this->request->getPost();

            //если пользователь не админ - тофильтровать результат по его id
            if (!$token->user->is_admin) $data['user_id'] = $token->user_id;

            return Content::get($data);

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
                'content_type' => 'required|in:0,1',
                'photos' => 'required_id:content_type,0',
                'video' => 'required_id:content_type,1',
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

                if ($content->content_type == 0)
                    $content->attachImage($file);
                else
                    $content->attachVideo($file);

                $contentArray = $content->toArray(Content::$fields);

                /*if ($content->content_type == 0)
                    $contentArray['image'] = $content->image->toArray();
                else
                    $contentArray['video'] = $content->video->toArray();*/
                
                $result[] = $contentArray;
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

            $data = $this->request->getPost();

            $rules = [
                'id' => 'required|integer',
                'description' => 'required'
            ];

            if (!Content::validateData($rules, $data)) throw new ValidationException(Content::$validationMessages);

            $content = Content::findFirstById($data['id']);

            if (!$content) throw new ApiException(ApiException::OBJECT_NOT_FOUND);

            if (!$token->user->is_admin && $content->user_id != $token->user_id) throw new ApiException(ApiException::OBJECT_ACCESS);

            $content->description = $data['description'];
            $content->save();

            return ['success' => true];
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

