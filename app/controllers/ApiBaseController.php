<?php namespace BeriDelay\Controllers;

use Carbon\Carbon;
use System\Controllers\Controller;
use Phalcon\Mvc\Dispatcher;
use BeriDelay\Models\User;
use BeriDelay\Models\Token;
use System\Exceptions\BaseException;
use System\Exceptions\ValidationException;
use BeriDelay\Exceptions\ApiException;

/**
 * Базовый контроллер API
 * @package BeriDelay\Controllers
 */
class ApiBaseController extends Controller
{
    public $user = null;

    public function beforeExecuteRoute(Dispatcher $dispatcher)
    {
        try {
            $this->view->disable();

            $method = $dispatcher->getActionName();

            if ($method == 'show404') return true;

            if (!$this->request->isPost()) throw new ApiException(ApiException::BAD_REQUEST);
        } catch (BaseException $e) {
            return $this->errorException($e);
        }
        return true;
    }

    /**
     * @return Token
     * @throws ApiException
     */
    public function hasPrivate()
    {
        if(!$token_string = $this->request->getPost('token_access')) throw new ApiException(ApiException::TOKEN_REQUIRED);
        
        if(!$token = Token::getByToken($token_string)) throw new ApiException(ApiException::TOKEN_INVALID);

        if ($token->last_seen->addWeek() < Carbon::now()) {
            $token->delete();
            throw new ApiException(ApiException::TOKEN_INVALID);
        }

        $token->life();

        if (!$token->user) {
            $token->delete();
            throw new ApiException(ApiException::TOKEN_INVALID);
        }

        $this->di->set('user', $token->user, true);

        return $token;
    }

    public function afterExecuteRoute(Dispatcher $dispatcher)
    {
        $data = $dispatcher->getReturnedValue();
        if ($data === false) return false;

        $this->send($data);
        return true;
    }

    public function error($code, $method, $controller = null, $params = null, $extra = [], $message = 'Unknown error')
    {
        if ($params == null) $params = $this->request->getPost();

        unset($params['token_access']);
        unset($params['token_refresh']);

        if ($controller == null) $controller = $this->dispatcher->getControllerName();

        $result = [
            'code' => $code,
            'message' => $message,
            'method' => $controller . '/' . $method,
            'params' => $params
        ];
        $this->send(['error' => $result + $extra]);

        return false;
    }

    public function send($data)
    {
        if ($this->request->getPost('state') !== null) $data = $data + ['state' => $this->request->getPost('state')];
        $this->response->setJsonContent($data);
        $this->response->setContentType('application/json');
        $this->response->setHeader('Access-Control-Allow-Origin','*');
        $this->response->send();
        exit;
    }

    public function show404Action($method, $controller = null)
    {
        return $this->errorException(new ApiException(ApiException::NOT_FOUND, []), $method, $controller);
    }
    
    public function errorException(BaseException $exception, $method = null, $controller = null)
    {
        if ($method == null) $method = $this->dispatcher->getActionName();
        return $this->error($exception->getCode(), $method, $controller, null, $exception->getInfo(), $exception->getMessage());
    }

    public function delete($model)
    {
        try {
            //приватный метод
            $token = $this->hasPrivate();

            //проверяем переданные параметры
            if (!$this->request->getPost('ids') && !$this->request->getPost('id')) throw new ApiException(ApiException::PARAM_REQUIRED);
            $ids = $this->request->getPost('ids') ? $this->request->getPost('ids') : [$this->request->getPost('id')];

            //пользователь должен быть админом
            if (!$token->user->is_admin) throw new ApiException(ApiException::FORBIDDEN);

            $results = $deleted = [];
            $success = true;

            //получаем экземпляр конструктора запросов
            $query = call_user_func([$model, 'query']);

            //в цикле удаляем все указанные записи
            foreach ($query->inWhere('id', $ids)->execute() as $model) {
                /** @var \System\Models\Model $model */
                $result = ['id' => $model->id, 'success' => true];
                $deleted[] = $model->id;

                //TODO: отслеживание ошибок при удалении
                $model->delete();

                $results[]  = $result;
            }

            if (count($deleted) != count($ids)) {
                $success = false;
                $diff = array_diff($ids, $deleted);
                foreach ($diff as $id) $results[] = ['id' => $id, 'success' => false, 'error' => ['code' => 404, 'message' => 'Запись не существует, либо к ней нет доступа']];
            }
        } catch (BaseException $e) {
            return $this->errorException($e);
        }

        return ['success' => $success, 'results' => $results];
    }
}
