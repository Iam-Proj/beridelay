<?php

namespace System\Helpers;

use Phalcon\Mvc\User\Component;

class Captcha extends Component {
    
    private static $secretKey = '6LeotSITAAAAAFyVb9gm3DHKqb0WONhSbBUhHjam';
    public static $publicKey  = '6LeotSITAAAAAGsK4givVUHWvsujFaTf9iOof-tG';
    public static $remoteUrl  = 'https://www.google.com/recaptcha/api/siteverify';
    public static $postFields;
    public static $response;
    public static $status;
    public static $mObj;
    
    public function init(){
        self::$mObj = new self();
    }
    
    public static function getFormElement(){
        return '<div class="g-recaptcha" data-sitekey="'.self::$publicKey.'"></div>';
    }
    
    public static function getFrontScript(){
        return '<script src="https://www.google.com/recaptcha/api.js"></script>';
    }
    
    private static function formedPost($postData){
        self::$postFields['secret'] = self::$secretKey;
        if(isset($postData['g-recaptcha-response']) && !empty($postData['g-recaptcha-response'])){
            self::$postFields['response'] = $postData['g-recaptcha-response'];
            return true;
        }
    }
    
    public static function checkCaptcha($postData) {
        if(!self::formedPost($postData)){ return false; }
        $s = curl_init();
        curl_setopt($s, CURLOPT_URL, self::$remoteUrl);
        curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($s, CURLOPT_POST, true);
        curl_setopt($s, CURLOPT_POSTFIELDS, self::$postFields);
        self::$response = curl_exec($s); 
        curl_close($s);
        return self::checkResponse();
    }
    
    private static function checkResponse(){
        $arrStatus = json_decode(self::$response,TRUE);
        if(isset($arrStatus['success']) && $arrStatus['success'] == true){
            return true;
        }
        return false;
    }
    
}