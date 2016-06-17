<?php namespace System\Helpers;

use Phalcon\Mvc\User\Component;

class Captcha extends Component {
    
    private static $secretKey = '6LeotSITAAAAAFyVb9gm3DHKqb0WONhSbBUhHjam';
    public static $publicKey  = '6LeotSITAAAAAGsK4givVUHWvsujFaTf9iOof-tG';
    public static $remoteUrl  = 'https://www.google.com/recaptcha/api/siteverify';
    public static $scriptUrl = 'https://www.google.com/recaptcha/api.js';
    public static $fieldName = 'g-recaptcha-response';
    public static $response;
    public static $status;
    public static $mObj;
    
    public function init()
    {
        self::$mObj = new self();
    }

    /**
     * Возвращает HTML-код элемента капчи
     * @return string
     */
    public static function getFormElement()
    {
        return '<div class="g-recaptcha" data-sitekey="' . self::$publicKey . '"></div>';
    }

    /**
     * Возвращает HTML-код JS скрипта
     * @return string
     */
    public static function getFrontScript()
    {
        return '<script src="' . self::$scriptUrl . '"></script>';
    }
    
    private static function formedPost($value)
    {
        $result = [
            'secret' => self::$secretKey,
            'response' => $value
        ];

        return $result;
    }

    /**
     * Проверяет, прошел ли пользователь капчу
     * @param string $value Значение поля g-recaptcha-response
     * @return bool
     */
    public static function checkCaptcha($value)
    {
        $fields = self::formedPost($value);
        $s = curl_init();
        curl_setopt($s, CURLOPT_URL, self::$remoteUrl);
        curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($s, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($s, CURLOPT_POST, true);
        curl_setopt($s, CURLOPT_POSTFIELDS, $fields);

        self::$response = curl_exec($s);

        curl_close($s);
        
        return self::checkResponse();
    }

    private static function checkResponse()
    {
        $arrStatus = json_decode(self::$response, true);
        if(isset($arrStatus['success']) && $arrStatus['success'] == true) return true;
        return false;
    }
    
}