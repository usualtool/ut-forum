<?php
namespace Controller\Authlogin\Admin;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Authlogin\Authlogin as AuthloginModel;
class Index{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $app;
        $model=new AuthloginModel();
        $l=$_GET["l"] ?? "";
        $do=$_GET["do"] ?? "";
        $app->Runin(array("l","data"),array($l,$model->GetList()));
        $app->Open("index.cms");
        if($do=="update"){
            $id=Inc::SqlCheck($_POST["id"] ?? "");
            $qq_appid=Inc::SqlCheck($_POST["qq_appid"] ?? "");
            $qq_appkey=Inc::SqlCheck($_POST["qq_appkey"] ?? "");
            $qq_reurl=Inc::SqlCheck($_POST["qq_reurl"] ?? "");
            $wb_appid=Inc::SqlCheck($_POST["wb_appid"] ?? "");
            $wb_appkey=Inc::SqlCheck($_POST["wb_appkey"] ?? "");
            $wb_reurl=Inc::SqlCheck($_POST["wb_reurl"] ?? "");
            $wx_appid=Inc::SqlCheck($_POST["wx_appid"] ?? "");
            $wx_appkey=Inc::SqlCheck($_POST["wx_appkey"] ?? "");
            $wx_reurl=Inc::SqlCheck($_POST["wx_reurl"] ?? "");
            if($l=="qq"):
                $model->Update(array("qq_appid"=>$qq_appid,"qq_appkey"=>$qq_appkey,"qq_reurl"=>$qq_reurl),"id='$id'");
                Inc::GoUrl(Route::Link("authlogin", "", "l=qq"),"保存成功!");
            endif;
            if($l=="weibo"):
                $model->Update(array("wb_appid"=>$wb_appid,"wb_appkey"=>$wb_appkey,"wb_reurl"=>$wb_reurl),"id='$id'");
                Inc::GoUrl(Route::Link("authlogin", "", "l=weibo"),"保存成功!");
            endif;
            if($l=="wechat"):
                $model->Update(array("wx_appid"=>$wx_appid,"wx_appkey"=>$wx_appkey,"wx_reurl"=>$wx_reurl),"id='$id'");
                Inc::GoUrl(Route::Link("authlogin", "", "l=wechat"),"保存成功!");
            endif;
        }
    }
}