<?php
namespace Controller\Forum\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Forum\Member as MemberModel;
use Model\Forum\Session as SessionModel;
use Model\Forum\Power as PowerModel;
class My_black{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $app;
        $member=new MemberModel();
        $power=(new PowerModel())->Check();
        if(empty($power["login"])):
            Inc::GoUrl(Route::Link("forum","login","goto=".urlencode(base64_encode(Inc::CurPageUrl()))),"请登陆!");
        endif;
        $session=(new SessionModel())->Check();
        $app->Runin(
            array("rate","user","uid","utype","username","money","avatar"),
            array($session["rate"],$session["user"],$session["uid"],$session["utype"],$session["username"],$session["money"],$session["avatar"])
        );
        $app->Runin("webplace","锁定/解除限制");
        $app->Open("my_black.cms");
        if(($_GET["do"] ?? "")=="black"){
            $black=Inc::SqlCheck($_POST["black"] ?? "");
            $username=Inc::SqlCheck($_POST["username"] ?? "");
                if($member->ExistsName($username)==0):
                    Inc::GoUrl("-1","操作账户不存在!");
                else:
                    if($member->SetBlack($username,$black)):
                        Inc::GoUrl(Route::Link("forum","my_black"),"操作成功!");
                    else:
                        Inc::GoUrl("-1","操作失败!");
                    endif;
                endif;
        }
    }
}