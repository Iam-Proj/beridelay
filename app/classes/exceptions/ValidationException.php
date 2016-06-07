<?php namespace System\Exceptions;

class ValidationException extends BaseException
{
    const PARAM_REQUIRED = 120;
    const PARAM_FORMAT = 121;

    protected $messages = [
        self::PARAM_REQUIRED => 'Не передан один или несколько из обязательных параметров',
        self::PARAM_FORMAT => 'Неверный формат одного или нескольких переданных параметров',
    ];

    public function __construct(array $validationMessages) {
        if (count($validationMessages['required'])) parent::__construct(ValidationException::PARAM_REQUIRED, ['fields' => $validationMessages['required']]);
        parent::__construct(ValidationException::PARAM_FORMAT, ['fields' => $validationMessages['format']]);
    }

}