<?php
namespace Controller\Forum\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Forum\Forum as ForumModel;
use Model\Forum\Session as SessionModel;
use Model\Forum\Power as PowerModel;
class My_admin_black{
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
        $utype=$session["utype"];
        if($utype!=99999):
            Inc::GoUrl("-1","权限不足!");
        endif;
        $pagelink="";

        $page=empty($_GET["page"]) ? 1 : $_GET["page"];
        $pagenum=10;
        $data=$model->GetClosedPosts($page,$pagenum);
        $querynum=$data["querynum"];
        $querydata=$data["querydata"];
        $totalpage=ceil($querynum/$pagenum);
        $app->Runin("data",$querydata);
        $app->Runin(array("webplace","total","curpage","listnum","pagelink"),array("屏蔽管理",$totalpage,$page,$pagenum,$pagelink));
        $app->Open("my_admin_black.cms");
    }
}