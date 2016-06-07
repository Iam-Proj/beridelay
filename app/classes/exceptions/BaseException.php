<?php namespace System\Exceptions;

use Exception;

class BaseException extends Exception
{
    protected $messages = [];
    protected $info;

    public function __construct($code = 0, $info = []) {
        $this->info = $info;
        parent::__construct($this->getMessageByCode($code), $code);
    }

    protected function getMessageByCode($code)
    {
        if (isset($this->messages[$code])) return $this->messages[$code];
        return 'Unknown error';
    }

    public function getInfo()
    {
        return $this->info;
    }
}