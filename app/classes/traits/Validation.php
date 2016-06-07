<?php namespace System\Traits;

use Illuminate\Validation\Validator;
use Phalcon\Mvc\Model\Message;
use Symfony\Component\Translation\Translator;
use System\Exceptions\ValidationException;


/**
 * Трайт "Валидация"
 * Использует Illuminate Validator для валидации полей модели
 * @package System\Traits
 */
trait Validation
{
    protected $validation = [];
    protected static $validationMessages = [
        'required' => [],
        'format' => [],
        'errors' => []
    ];

    public function validation()
    {
        $result = self::validateData($this->validation, $this->toArray());
        if (!$result)
            foreach (self::$validationMessages['errors'] as $error) $this->appendMessage($error);

        return $result;
    }

    public static function validateData($rules = null, $data = null)
    {
        if (!count($rules)) return true;

        $validation = new Validator(new Translator('ru'), $data, $rules);

        if ($validation->fails()) {
            $required = [];
            $format = [];

            foreach ($validation->messages()->getMessages() as $field => $errors) {
                foreach ($errors as $error) {
                    self::$validationMessages['errors'][] = new Message('', $field, $error);

                    if ($error == 'Required')
                        $required[$field] = 'Required';
                    else
                        $format[$field] = $error;
                }
            }

            self::$validationMessages['required'] = $required;
            self::$validationMessages['format'] = $format;

            return false;
        }

        return true;
    }

}