<?php namespace BeriDelay\Controllers;

use Carbon\Carbon;
use System\Controllers\Controller;
use Phalcon\Mvc\Dispatcher;
use Illuminate\Validation\Validator;
use Symfony\Component\Translation\Translator;
use BeriDelay\Models\User;
use BeriDelay\Models\Token;

/**
 * Базовый контроллер API
 * @package BeriDelay\Controllers
 */
class ApiBaseController extends Controller
{
    const ERROR_OBJECT_ACCESS = 103;
    const ERROR_OBJECT_NOT_FOUND = 104;
    const ERROR_TOKEN_INVALID = 110;
    const ERROR_REFRESH_INVALID = 111;
    const ERROR_TOKEN_REQUIRED = 112;
    const ERROR_PARAM_REQUIRED = 120;
    const ERROR_PARAM_FORMAT = 121;
    const ERROR_CAPTCHA = 130;
    const ERROR_BAD_REQUEST = 400;
    const ERROR_UNAUTHORIZED = 401;
    const ERROR_FORBIDDEN = 403;
    const ERROR_NOT_FOUND = 404;
    const ERROR_METHOD_NOT_ALLOWED = 405;
    const ERROR_TOO_MANY_REQUESTS = 429;
    const ERROR_INTERNAL = 500;

    const ERROR_INVITE_NOT_FOUND = 209;
    const ERROR_EMAIL_EXISTS = 210;
    const ERROR_PHONE_EXISTS = 211;

    const ERROR_ACCOUNT_NOT_FOUND = 250;
    const ERROR_ACCOUNT_NOT_ACTIVATED = 251;
    const ERROR_ACCOUNT_BLOCKED = 252;

    protected $messages = [
        self::ERROR_OBJECT_ACCESS => 'Доступ к указанному объекту запрещен',
        self::ERROR_OBJECT_NOT_FOUND => 'Указанный объект не найден',
        self::ERROR_TOKEN_INVALID => 'token_access устарел или недействителен',
        self::ERROR_REFRESH_INVALID => 'token_refresh устарел или недействителен',
        self::ERROR_TOKEN_REQUIRED => 'token_access не передан',
        self::ERROR_PARAM_REQUIRED => 'Не передан один или несколько из обязательных параметров',
        self::ERROR_PARAM_FORMAT => 'Неверный формат одного или нескольких переданных параметров',
        self::ERROR_CAPTCHA => 'Запрос ввода капчи',
        self::ERROR_BAD_REQUEST => 'Неверный запрос',
        self::ERROR_UNAUTHORIZED => 'Для доступа к указанному методу нужна авторизация',
        self::ERROR_FORBIDDEN => 'Доступ к указанному методу запрещен',
        self::ERROR_NOT_FOUND => 'Указанный метод не найден',
        self::ERROR_METHOD_NOT_ALLOWED => 'Метод не поддерживается',
        self::ERROR_TOO_MANY_REQUESTS => 'Слишком много запросов',
        self::ERROR_INTERNAL => 'Внутренняя ошибка сервера',

        self::ERROR_INVITE_NOT_FOUND => 'Указанного приглашения не существует',
        self::ERROR_EMAIL_EXISTS => 'Пользователь с таким email уже зарегистрирован',
        self::ERROR_PHONE_EXISTS => 'Пользователь с таким номером телефона уже зарегистрирован',

        self::ERROR_ACCOUNT_NOT_FOUND => 'Пользователь с указанным email и паролем не найдены в системе',
        self::ERROR_ACCOUNT_NOT_ACTIVATED => 'Аккаунт пользователя не активирован',
        self::ERROR_ACCOUNT_BLOCKED => 'Аккаунт пользователя заблокирован',
    ];

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
     * Здесь хранятся переданные параметры, отфильтрованные по необходимому методу, а также отвалидированные
     * @var array
     */
    protected $parameters = [];

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
        $this->view->disable();

        $method = $dispatcher->getActionName();

        if ($method == 'show404') return true;

        if (!isset($this->actions[$method])) {
            return $this->error(self::ERROR_NOT_FOUND, $method);
        }

        if (!$this->request->isPost()) {
            return $this->error(self::ERROR_BAD_REQUEST, $method);
        }

        $action = $this->actions[$method];
        $private = isset($action['isPrivate']) ? $action['isPrivate'] : false;
        $fields = isset($action['fields']) ? $action['fields'] : [];

        $data = [];
        foreach ($fields as $key => $field) $data[$key] = $this->request->getPost($key);
        $this->state = $this->request->getPost('state');

        if ($private) {
            $data['token_access'] = $this->request->getPost('token_access');
            if (!strlen($data['token_access'])) return $this->error(self::ERROR_TOKEN_REQUIRED, $method);

            $token = Token::getByToken($data['token_access']);
            if (!$token) return $this->error(self::ERROR_TOKEN_INVALID, $method);

            if ($token->updated_at->addHour() < Carbon::now()) {
                $token->delete();
                return $this->error(self::ERROR_TOKEN_INVALID, $method);
            }

            $token->save();
            $this->token = $token;
            $this->user = $token->user;
        }


        $validation = new Validator(new Translator('ru'), $data, $fields);

        if ($validation->fails()) {
            $required = [];
            $format = [];

            foreach ($validation->failed() as $field => $types) {
                foreach ($types as $type => $options) {
                    if ($type == 'Required')
                        $required[$field] = 'Required';
                    else
                        $format[$field] = $type;
                }
            }
            if (count($required)) return $this->error(self::ERROR_PARAM_REQUIRED, $method, null, null, ['fields' => $required]);
            return $this->error(self::ERROR_PARAM_FORMAT, $method, null, null, ['fields' => $format]);
        }
        $this->parameters = $data;
    }

    public function afterExecuteRoute(Dispatcher $dispatcher)
    {
        $data = $dispatcher->getReturnedValue();
        if ($data === false) return false;

        $this->send($data);
    }

    public function error($code, $method, $controller = null, $params = null, $extra = [])
    {
        if ($params == null) $params = $_POST;

        unset($params['token_access']);
        unset($params['token_refresh']);

        if ($controller == null) $controller = $this->dispatcher->getControllerName();

        $result = [
            'code' => $code,
            'message' => $this->getErrorMessage($code),
            'method' => $controller . '/' . $method,
            'params' => $params
        ];
        $this->send(['error' => $result + $extra]);

        return false;
    }

    public function getErrorMessage($code)
    {
        if (isset($this->messages[$code])) return $this->messages[$code];
        return 'Unknown error';
    }

    public function send($data)
    {
        if ($this->state !== null) $data = $data + ['state' => $this->state];
        $this->response->setJsonContent($data);
        $this->response->setContentType('application/json');
        $this->response->send();
        exit;
    }

    public function show404Action($method, $controller = null)
    {
        return $this->error(self::ERROR_NOT_FOUND, $method, $controller);
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
