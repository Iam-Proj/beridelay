<?php namespace BeriDelay\Controllers;

use System\Helpers\Captcha;

class TestController extends \Phalcon\Mvc\Controller {
    
    public function captchaAction(){
        
        if($postData = $this->request->getPost()){
            if(Captcha::checkCaptcha($this->request->getPost())){
                $this->view->setVar('captcha_success','Капча успешно подтверждена');
            }
        }
        
        $this->view->setVar('captcha_script',Captcha::getFrontScript());
        $this->view->setVar('captcha_element',Captcha::getFormElement());
    }
    
}   