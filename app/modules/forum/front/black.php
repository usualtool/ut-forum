<?php
namespace Controller\Forum\Front;
use Model\Forum\Member as MemberModel;
use Model\Forum\Session as SessionModel;
class Black{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $app;
        $member=new MemberModel();
        $session=(new SessionModel())->Check();
        $app->Runin(
            array("rate","user","uid","utype","username","money","avatar"),
            array($session["rate"],$session["user"],$session["uid"],$session["utype"],$session["username"],$session["money"],$session["avatar"])
        );
        $data=$member->GetBlackList();
        $app->Runin(array("webplace","data"),array("小黑屋处罚公示",$data));
        $app->Open("black.cms");
    }
}