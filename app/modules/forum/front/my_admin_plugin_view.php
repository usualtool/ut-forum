<?php
namespace Controller\Forum\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Forum\Plugin as PluginModel;
use Model\Forum\Session as SessionModel;
use Model\Forum\Power as PowerModel;
class My_admin_plugin_view{
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
        $pid=Inc::SqlCheck($_GET["pid"] ?? "");
        $data=$plugin->GetDetail($pid);
        $title=$data["querydata"][0]["title"];
        $plugin_file=$plugin->Sync($pid);
        $app->Runin(
            array("webplace","data","pid","plugin_file"),
            array($title,$data["querydata"],$pid,$plugin_file)
        );
        $app->Open("my_admin_plugin_view.cms");
    }
}