<?php
namespace Controller\Payment\Admin;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Payment\Pay as PayModel;
class Index{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $app;
        $model=new PayModel();
        $l=$_GET["l"] ?? "";
        $app->Runin(array("l","data"),array($l,$model->GetList()));
        if(($_GET["do"] ?? "")=="update"){
            $id=Inc::SqlCheck($_POST["id"] ?? "");
            $ali_appid=Inc::SqlCheck($_POST["ali_appid"] ?? "");
            $ali_private_key=Inc::SqlCheck($_POST["ali_private_key"] ?? "");
            $ali_public_key=Inc::SqlCheck($_POST["ali_public_key"] ?? "");
            $ali_notify_url=Inc::SqlCheck($_POST["ali_notify_url"] ?? "");
            $ali_return_url=Inc::SqlCheck($_POST["ali_return_url"] ?? "");
            $wx_appid=Inc::SqlCheck($_POST["wx_appid"] ?? "");
            $wx_mchid=Inc::SqlCheck($_POST["wx_mchid"] ?? "");
            $wx_key=Inc::SqlCheck($_POST["wx_key"] ?? "");
            $wx_secert=Inc::SqlCheck($_POST["wx_secert"] ?? "");
            $wx_notify_url=Inc::SqlCheck($_POST["wx_notify_url"] ?? "");
            $pp_mod=Inc::SqlCheck($_POST["pp_mod"] ?? "");
            $pp_clientid=Inc::SqlCheck($_POST["pp_clientid"] ?? "");
            $pp_secret=Inc::SqlCheck($_POST["pp_secret"] ?? "");
            $pp_return_url=Inc::SqlCheck($_POST["pp_return_url"] ?? "");
            $pp_notify_url=Inc::SqlCheck($_POST["pp_notify_url"] ?? "");
            $url_ok=Inc::SqlCheck($_POST["url_ok"] ?? "");
            $url_no=Inc::SqlCheck($_POST["url_no"] ?? "");
            $sql=0;
            if($l=="alipay"):
                $sql=$model->SaveConfig(array(
                    "ali_appid"=>$ali_appid,
                    "ali_private_key"=>$ali_private_key,
                    "ali_public_key"=>$ali_public_key,
                    "ali_notify_url"=>$ali_notify_url,
                    "ali_return_url"=>$ali_return_url),$id);
            elseif($l=="wechat"):
                $sql=$model->SaveConfig(array(
                    "wx_appid"=>$wx_appid,
                    "wx_mchid"=>$wx_mchid,
                    "wx_key"=>$wx_key,
                    "wx_secert"=>$wx_secert,
                    "wx_notify_url"=>$wx_notify_url),$id);
            elseif($l=="paypal"):
                $sql=$model->SaveConfig(array(
                    "pp_mod"=>$pp_mod,
                    "pp_clientid"=>$pp_clientid,
                    "pp_secret"=>$pp_secret,
                    "pp_return_url"=>$pp_return_url,
                    "pp_notify_url"=>$pp_notify_url),$id);
            elseif($l=="url"):
                $sql=$model->SaveConfig(array(
                    "url_ok"=>$url_ok,
                    "url_no"=>$url_no),$id);
            endif;
            if($sql):
                Inc::GoUrl(Route::Link("payment", "index", "l=".$l),"操作成功!");
            else:
                Inc::GoUrl(Route::Link("payment", "index", "l=".$l),"操作失败!");
            endif;
        }
        $app->Open("index.cms");
    }
}