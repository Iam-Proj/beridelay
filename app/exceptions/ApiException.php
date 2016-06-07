<?php namespace BeriDelay\Exceptions;

use System\Exceptions\BaseException;

class ApiException extends BaseException
{
    const OBJECT_ACCESS = 103;
    const OBJECT_NOT_FOUND = 104;
    const TOKEN_INVALID = 110;
    const REFRESH_INVALID = 111;
    const TOKEN_REQUIRED = 112;
    const CAPTCHA = 130;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const TOO_MANY_REQUESTS = 429;
    const PARAMS_ERROR = 501;

    protected $messages = [
        self::OBJECT_ACCESS => 'Доступ к указанному объекту запрещен',
        self::OBJECT_NOT_FOUND => 'Указанный объект не найден',
        self::TOKEN_INVALID => 'token_access устарел или недействителен',
        self::REFRESH_INVALID => 'token_refresh устарел или недействителен',
        self::TOKEN_REQUIRED => 'token_access не передан',
        self::CAPTCHA => 'Запрос ввода капчи',
        self::BAD_REQUEST => 'Неверный запрос',
        self::UNAUTHORIZED => 'Для доступа к указанному методу нужна авторизация',
        self::FORBIDDEN => 'Доступ к указанному методу запрещен',
        self::NOT_FOUND => 'Указанный метод не найден',
        self::METHOD_NOT_ALLOWED => 'Метод не поддерживается',
        self::TOO_MANY_REQUESTS => 'Слишком много запросов',
        self::PARAMS_ERROR => 'Ошибка при передачи параметров',
    ];
}