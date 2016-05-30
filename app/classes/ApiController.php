<?php namespace System\Controllers;

use Phalcon\Mvc\Dispatcher;
use Illuminate\Validation\Validator;
use Symfony\Component\Translation\Translator;

class ApiController extends Controller
{
    protected $actions = [];

    protected $parameters = [];

    protected $messages = [
        103 => 'Доступ к указанному объекту запрещен',
        104 => 'Указанный объект не найден',
        110 => 'token_access устарел или недействителен',
        111 => 'token_refresh устарел или недействителен',
        112 => 'token_access не передан',
        120 => 'Не передан один или несколько из обязательных параметров',
        121 => 'Неверный формат одного или нескольких переданных параметров',
        130 => 'Запрос ввода капчи',
        400 => 'Неверный запрос',
        401 => 'Для доступа к указанному методу нужна авторизация',
        403 => 'Доступ к указанному методу запрещен',
        404 => 'Указанный метод не найден',
        405 => 'Метод не поддерживается',
        429 => 'Слишком много запросов',
        500 => 'Внутренняя ошибка сервера',
    ];

    public function beforeExecuteRoute(Dispatcher $dispatcher)
    {
        $this->view->disable();

        $method = $dispatcher->getActionName();
        $controller = $dispatcher->getControllerName();

        if (!isset($this->actions[$method])) {
            return $this->error(404, $controller . '/' . $method);
        }

        if (!$this->request->isPost()) {
            return $this->error(400, $controller . '/' . $method);
        }

        $action = $this->actions[$method];

        $data = [];
        foreach ($action['fields'] as $key => $field) $data[$key] = $this->request->getPost($key);

        $validation = new Validator(new Translator('ru'), $data, $action['fields']);

        if ($validation->fails()) {
            var_dump($validation->errors()); exit;
        }
    }

    public function afterExecuteRoute(Dispatcher $dispatcher)
    {
        $data = $dispatcher->getReturnedValue();
        $this->response->setJsonContent($data);
        $this->response->send();
    }

    public function error($code, $method, $params = null, $extra = [])
    {
        if ($params == null) $params = $_POST;

        unset($params['token_access']);
        unset($params['token_refresh']);

        $result = [
            'code' => $code,
            'message' => $this->getErrorMessage($code),
            'method' => $method,
            'params' => $params
        ];
        $this->response->setJsonContent($result + $extra);
        $this->response->send();

        return false;
    }

    public function getErrorMessage($code)
    {
        if (isset($this->messages[$code])) return $this->messages[$code];
        return 'Unknown error';
    }
}
