<?php namespace System\Exceptions;

class UserException extends BaseException
{
    const NOT_FOUND = 250;
    const NOT_ACTIVATED = 251;
    const BLOCKED = 252;

    protected $messages = [
        self::NOT_FOUND => 'Пользователь с указанным email и паролем не найдены в системе',
        self::NOT_ACTIVATED => 'Аккаунт пользователя не активирован',
        self::BLOCKED => 'Аккаунт пользователя заблокирован',
    ];
}