<?php
namespace Controller\Forum\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Forum\Forum as ForumModel;
use Model\Forum\Member as MemberModel;
use Model\Forum\Session as SessionModel;
class Auth{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $app;
        $model=new ForumModel();
        $member=new MemberModel();
        $session=new SessionModel();
        $cookname=$model->GetCookieName();
        $data=$member->GetByOpenid($_COOKIE["auth_openid"] ?? "");
        if($data["querynum"]==1){
            setcookie("auth_openid","");
            setcookie("auth_name","");
            setcookie("auth_avatar","");
            $rows=$data["querydata"][0];
            if($rows["black"]==0){
                $member->RecordLogin($rows["id"]);
                $session->SetLogin($cookname,$rows["id"],$rows["username"]);
                session_regenerate_id(TRUE);
                Inc::GoUrl(Route::Link("forum"),"第三方授权登陆成功!");
            }else{
                Inc::GoUrl(Route::Link("forum"),"账户已被封禁!");
            }
        }else{
            $app->Runin("webplace","第三方账号授权");
            $app->Open("auth.cms");
        }
        if(($_GET["do"] ?? "")=="opensave"){
            $name=Inc::SqlCheck($_POST["name"] ?? "");
            $newid=$member->AddOpenid(
                Inc::SqlCheck($_POST["openid"] ?? ""),
                Inc::SqlCheck($_POST["avatar"] ?? ""),
                $name);
            if($newid){
                setcookie("auth_openid","");
                setcookie("auth_name","");
                setcookie("auth_avatar","");
                $member->RecordLogin($newid);
                $session->SetLogin($cookname,$newid,$name);
                session_regenerate_id(TRUE);
                Inc::GoUrl(Route::Link("forum"),"第三方授权登陆成功!");
            }else{
                Inc::GoUrl(Route::Link("forum"),"一键登录失败!");
            }
        }
    }
}