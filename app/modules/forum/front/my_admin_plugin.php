<?php
namespace Controller\Forum\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Forum\Plugin as PluginModel;
use Model\Forum\Session as SessionModel;
use Model\Forum\Power as PowerModel;
class My_admin_plugin{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $app;
        $plugin=new PluginModel();
        $power=(new PowerModel())->Check();
        if(empty($power["login"])):
            Inc::GoUrl(Route::Link("forum","login","goto=".urlencode(base64_encode(Inc::CurPageUrl()))),"请登陆!");
        endif;
        $session=(new SessionModel())->Check();
        $app->Runin(
            array("rate","user","uid","utype","username","money","avatar"),
            array($session["rate"],$session["user"],$session["uid"],$session["utype"],$session["username"],$session["money"],$session["avatar"])
        );
        $utype=$session["utype"];
        if($utype!=99999):
            Inc::GoUrl("-1","权限不足!");
        endif;
        $t=$_GET["t"] ?? "";
        $do=$_GET["do"] ?? "";
        $app->Runin(array("webplace"),array("插件"));
        $app->Runin("plugin",$plugin->GetList($t));
        $app->Runin("t",$t);
        $app->Open("my_admin_plugin.cms");
        if($do=="install"){
            $d=$_GET["d"] ?? "";
            $pid=str_replace(".","",Inc::SqlCheck($_GET["pid"] ?? ""));
            $res=$plugin->Install($pid);
            if($res=="denied"):
                Inc::GoUrl("-1","安装权限不足!");
            endif;
            if($res=="zip"):
                Inc::GoUrl("-1","plugins目录775权限不足!");
            endif;
            if($res=="sql"):
                Inc::GoUrl("-1","插件安装失败!");
            endif;
            Inc::GoUrl(Route::Link("forum","my_admin_plugin","t=".$d),"成功安装插件!");
        }
        if($do=="uninstall"){
            $d=$_GET["d"] ?? "";
            $pid=str_replace(".","",Inc::SqlCheck($_GET["pid"] ?? ""));
            $res=$plugin->Uninstall($pid);
            if($res=="sql"):
                Inc::GoUrl("-1","插件卸载失败!");
            endif;
            Inc::GoUrl(Route::Link("forum","my_admin_plugin","t=".$d),"成功卸载插件!");
        }
    }
}