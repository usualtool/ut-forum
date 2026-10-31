<?php
namespace Controller\Forum\Front;
use usualtool\Lib\Code;
use Model\Forum\Session as SessionModel;
class Captcha{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        $captcha = new Code();
        $captcha->createImage();
        (new SessionModel())->SetAuthcode($captcha->GetCode());
    }
}