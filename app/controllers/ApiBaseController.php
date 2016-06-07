<?php namespace BeriDelay\Controllers;

use Carbon\Carbon;
use System\Controllers\Controller;
use Phalcon\Mvc\Dispatcher;
use Illuminate\Validation\Validator;
use Symfony\Component\Translation\Translator;
use BeriDelay\Models\User;
use BeriDelay\Models\Token;
use System\Exceptions\BaseException;
use BeriDelay\Exceptions\ApiException;

/**
 * Базовый контроллер API
 * @package BeriDelay\Controllers
 */
class ApiBaseController extends Controller
{
    /**
     * Настройки методов API
     * <code>
     * $action = [
     *  'actionName' => [       //имя метода
     *      'isPrivate' => true //приватный или нет метод (приватный обязательно требует token_access), по умолчанию false
     *      'fields' => [       //необходимые параметры и настройки валидации https://laravel.com/docs/5.1/validation
     *          'email' => 'required|email',
     *          'is_active' => 'in:0,1'
     *      ]
     *  ]
     * ];
     * </code>
     * @var array
     */
    protected $actions = [];

    /**
     * Здесь хранится текущий пользователь
     * @var User|null
     */
    protected $user = null;

    /**
     * Здесь хранится модель текущего токена
     * @var Token|null
     */
    protected $token = null;

    /**
     * Здесь хранятся переданные дополнительные параметры
     * @var mixed|null
     */
    protected $state = null;

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

    public function hasPrivate()
    {
        $token_string = $this->request->getPost('token_access');
        if (!strlen($token_string)) throw new ApiException(ApiException::TOKEN_REQUIRED);

        $token = Token::getByToken($token_string);
        if (!$token) throw new ApiException(ApiException::TOKEN_INVALID);
        
        if (!$token->user || $token->updated_at->addHour() < Carbon::now()) {
            $token->delete();
            throw new ApiException(ApiException::TOKEN_INVALID);
        }
        
        $token->life();

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
        if ($this->state !== null) $data = $data + ['state' => $this->request->getPost('state')];
        $this->response->setJsonContent($data);
        $this->response->setContentType('application/json');
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
        //проверяем переданные параметры
        if (!isset($this->parameters['ids']) && !isset($this->parameters['id'])) return $this->error(self::ERROR_PARAM_REQUIRED, 'delete');
        $ids = isset($this->parameters['ids']) ? $this->parameters['ids'] : [$this->parameters['id']];

        //пользователь должен быть админом
        if (!$this->user->is_admin) $this->error(self::ERROR_FORBIDDEN, 'delete');

        $results = $deleted = [];
        $success = true;

        //получаем экземпляр конструктора запросов
        $query = call_user_func([$model, 'query']);

        //в цикле удаляем все указанные записи
        foreach ($query->inWhere('id', $ids)->execute() as $model) {
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

        return ['success' => $success, 'results' => $results];
    }
}
