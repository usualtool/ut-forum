<?php
namespace Controller\Forum\Front;
use Model\Forum\Session as SessionModel;
class Session{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $app;
        $session=(new SessionModel())->Check();
        $app->Runin(
            array("rate","user","uid","utype","username","money","avatar"),
            array($session["rate"],$session["user"],$session["uid"],$session["utype"],$session["username"],$session["money"],$session["avatar"])
        );
    }
}