<?php namespace System\Traits;

use Illuminate\Validation\Validator;
use Phalcon\Mvc\Model\Message;
use Symfony\Component\Translation\Translator;

/**
 * Трайт "Валидация"
 * Использует Illuminate Validator для валидации полей модели
 * @package System\Traits
 */
trait Validation
{
    protected $validation = [];

    public function validation()
    {
        if (!count($this->validation)) return true;

        $validation = new Validator(new Translator('ru'), $this->toArray(), $this->validation);

        if ($validation->fails()) {
            foreach ($validation->messages()->getMessages() as $field => $errors) {
                foreach ($errors as $error) {
                    $this->appendMessage(new Message('', $field, $error));
                }
            }
            return false;
        }

        return true;
    }
}