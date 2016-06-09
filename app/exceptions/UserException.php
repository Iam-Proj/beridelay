<?php namespace BeriDelay\Exceptions;

use System\Exceptions\BaseException;

class UserException extends BaseException
{
    const NOT_FOUND = 250;
    const NOT_ACTIVATED = 251;
    const BLOCKED = 252;
    const INVITE_NOT_FOUND = 209;
    const EMAIL_EXISTS = 210;
    const PHONE_EXISTS = 211;
    const INCORRECT_PASSWORD = 215;
    const UNKNOWN_TOKEN = 260;

    protected $messages = [
        self::NOT_FOUND => 'Пользователь с указанными данными не найден в системе',
        self::NOT_ACTIVATED => 'Аккаунт пользователя не активирован',
        self::BLOCKED => 'Аккаунт пользователя заблокирован',
        self::INVITE_NOT_FOUND => 'Указанного приглашения не существует',
        self::EMAIL_EXISTS => 'Пользователь с таким email уже зарегистрирован',
        self::PHONE_EXISTS => 'Пользователь с таким номером телефона уже зарегистрирован',
        self::INCORRECT_PASSWORD => 'Старый пароль указан неверно',
        self::UNKNOWN_TOKEN => 'Указанный ключ недействителен',
    ];
}