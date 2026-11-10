<?php
namespace Controller\Forum\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Forum\Forum as ForumModel;
use Model\Forum\Session as SessionModel;
use Model\Forum\Power as PowerModel;
class My_admin_set{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $app;
        $model=new ForumModel();
        $power=(new PowerModel())->Check();
        if(empty($power["login"])):
            Inc::GoUrl(Route::Link("forum","login","goto=".urlencode(base64_encode(Inc::CurPageUrl()))),"请登陆!");
        endif;
        $session=(new SessionModel())->Check();
        $app->Runin(
            array("rate","user","uid","utype","username","money","avatar"),
            array($session["rate"],$session["user"],$session["uid"],$session["utype"],$session["username"],$session["money"],$session["avatar"])
        );
        $set=$session["set"];
        $utype=$session["utype"];
        if($utype!=99999):
            Inc::GoUrl("-1","权限不足!");
        endif;
        $l=$_GET["l"] ?? "";
        $c=$_GET["c"] ?? "";
        $payment=$model->GetPayConfig();
        $connect=$model->GetConnectConfig();
        $app->Runin(array("webplace","set","l","c","payment","connect"),array("管理设置",$set,$l,$c,$payment,$connect));
        $app->Open("my_admin_set.cms");
        if(($_GET["do"] ?? "")=="update"){
            $id=($_POST["id"] ?? "");
            $rate=($_POST["rate"] ?? "");
            $cookname=($_POST["cookname"] ?? "");
            if($model->UpdateSet(array("rate"=>$rate,"cookname"=>$cookname),"id=?",array(intval($id)))):
                Inc::GoUrl(Route::Link("forum","my_admin_set"),"保存设置成功!");
            else:
                Inc::GoUrl("-1","保存设置失败!");
            endif;
        }
        if(($_GET["do"] ?? "")=="payment"){
            $id=($_POST["id"] ?? "");
            $paydata=array(
                "ali_appid"=>($_POST["ali_appid"] ?? ""),
                "ali_private_key"=>urlencode(($_POST["ali_private_key"] ?? "")),
                "ali_public_key"=>urlencode(($_POST["ali_public_key"] ?? "")),
                "ali_notify_url"=>($_POST["ali_notify_url"] ?? ""),
                "ali_return_url"=>($_POST["ali_return_url"] ?? ""),
                "wx_appid"=>($_POST["wx_appid"] ?? ""),
                "wx_mchid"=>($_POST["wx_mchid"] ?? ""),
                "wx_key"=>($_POST["wx_key"] ?? ""),
                "wx_secert"=>($_POST["wx_secert"] ?? ""),
                "wx_notify_url"=>($_POST["wx_notify_url"] ?? ""),
                "pp_mod"=>($_POST["pp_mod"] ?? ""),
                "pp_clientid"=>($_POST["pp_clientid"] ?? ""),
                "pp_secret"=>($_POST["pp_secret"] ?? ""),
                "pp_return_url"=>($_POST["pp_return_url"] ?? ""),
                "pp_notify_url"=>($_POST["pp_notify_url"] ?? ""),
                "url_ok"=>($_POST["url_ok"] ?? ""),
                "url_no"=>($_POST["url_no"] ?? ""));
            $sql=$model->SavePay($id,$l,$paydata);
            if($sql):
                Inc::GoUrl(Route::Link("forum","my_admin_set","l=".$l)."#payment","操作成功!");
            else:
                Inc::GoUrl("-1","操作失败!");
            endif;
        }
        if(($_GET["do"] ?? "")=="connect"){
            $id=($_POST["id"] ?? "");
            $connectdata=array(
                "qq_appid"=>($_POST["qq_appid"] ?? ""),
                "qq_appkey"=>($_POST["qq_appkey"] ?? ""),
                "qq_reurl"=>($_POST["qq_reurl"] ?? ""),
                "wb_appid"=>($_POST["wb_appid"] ?? ""),
                "wb_appkey"=>($_POST["wb_appkey"] ?? ""),
                "wb_reurl"=>($_POST["wb_reurl"] ?? ""),
                "wx_appid"=>($_POST["wx_appid"] ?? ""),
                "wx_appkey"=>($_POST["wx_appkey"] ?? ""),
                "wx_reurl"=>($_POST["wx_reurl"] ?? ""));
            $model->SaveConnect($id,$c,$connectdata);
            Inc::GoUrl(Route::Link("forum","my_admin_set","c=".$c)."#connect","保存成功!");
        }
    }
}