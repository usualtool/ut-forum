<?php
namespace Controller\Forum\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Forum\Forum as ForumModel;
use Model\Forum\Member as MemberModel;
use Model\Forum\Session as SessionModel;
class Login{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $app;
        $model=new ForumModel();
        $member=new MemberModel();
        $session=new SessionModel();
        $goto=empty($_GET["goto"]) ? ($_POST["goto"] ?? "") : ($_GET["goto"] ?? "");
        $gourl=empty($goto) ? Route::Link("forum") : base64_decode(urldecode($goto));
        $cookname=$model->GetCookieName();
        $app->Runin(array("webplace","goto","gourl"),array("登陆",$goto,$gourl));
        if(($_GET["do"] ?? "")=="out"){
            $session->ClearLogin($cookname);
            Inc::GoUrl($gourl,"登出成功!");
        }
        if(($_GET["do"] ?? "")=="login"){
            $username=Inc::SqlCheck($_POST["username"] ?? "");
            $password=Inc::SqlCheck($_POST["password"] ?? "");
            $code=Inc::SqlCheck(strtolower($_POST["code"] ?? ""));
            if(($_SESSION['authcode'] ?? "")==$code){
                if(!empty($username) && !empty($password)){
                    $rows=$member->Login($username,$password);
                    if(is_array($rows)){
                        $session->SetLogin($cookname,$rows["id"],$rows["username"]);
                        session_regenerate_id(TRUE);
                        Inc::GoUrl($gourl,"登陆成功!");
                    }elseif($rows=="nopass"){
                        Inc::GoUrl("-1","账户或密码不匹配!");
                    }else{
                        Inc::GoUrl("-1","账户不存在或已被封禁!");
                    }
                }else{
                    Inc::GoUrl("-1","账户或密码不能为空!");
                }
            }else{
                Inc::GoUrl("-1","验证码不正确!");
            }
        }
        $app->Open("login.cms");
    }
}