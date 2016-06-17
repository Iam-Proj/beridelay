<?php namespace System\Traits;

use Illuminate\Validation\Validator;
use Phalcon\Mvc\Model\Message;
use Symfony\Component\Translation\Translator;
use System\Exceptions\ValidationException;
use System\Helpers\Captcha;

/**
 * Трайт "Валидация"
 * Использует Illuminate Validator для валидации полей модели
 * @package System\Traits
 */
trait Validation
{
    protected $validationExceptions = false;
    protected $validation = [];
    public static $validationMessages = [];

    public function validation()
    {
        $result = self::validateData($this->validation, $this->toArray());
        if (!$result) {
            if ($this->validationExceptions) {
                throw new ValidationException(self::$validationMessages);
            } else {
                foreach (self::$validationMessages['errors'] as $error) $this->appendMessage($error);
            }
        }

        return $result;
    }


    public static function validateData($rules = null, $data = [])
    {
        self::$validationMessages = [
            'required' => [],
            'format' => [],
            'errors' => []
        ];

        if (!count($rules)) return true;
        
        $validation = new Validator(new Translator('ru'), $data, $rules);

        $validation->addExtension('captcha', function($attribute, $value, $parameters, $validator) {
            return Captcha::checkCaptcha($value);
        });

        if ($validation->fails()) {
            $required = [];
            $format = [];

            foreach ($validation->messages()->getMessages() as $field => $errors) {
                foreach ($errors as $error) {
                    self::$validationMessages['errors'][] = new Message('', $field, $error);

                    if ($error == 'validation.required')
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