<?php namespace BeriDelay\Exceptions;

use System\Exceptions\BaseException;

class TaskException extends BaseException
{
    const TASK_NOT_FOUND = 720;

    protected $messages = [
        self::TASK_NOT_FOUND => 'Задание не найдено',
    ];
    
}