<?php
namespace Controller\Forum\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Forum\Forum as ForumModel;
use Model\Forum\Session as SessionModel;
use Model\Forum\Power as PowerModel;
class My_post_list{
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
        $uid=$session["uid"];
        $pagelink="";

        $page=empty($_GET["page"]) ? 1 : $_GET["page"];
        $pagenum=10;
        $data=$model->GetMemberPosts("uid='$uid'",$page,$pagenum);
        $querynum=$data["querynum"];
        $querydata=$data["querydata"];
        $totalpage=ceil($querynum/$pagenum);
        $app->Runin("data",$querydata);
        $app->Runin(array("webplace","total","curpage","listnum","pagelink"),array("主题列表",$totalpage,$page,$pagenum,$pagelink));
        $app->Open("my_post_list.cms");
        if(($_GET["do"] ?? "")=="del"){
            $id=Inc::SqlCheck($_GET["id"] ?? "");
            if($model->DeleteMemberPost($uid,$id)):
                Inc::GoUrl(Route::Link("forum","my_post_list"),"删除成功!");
            else:
                Inc::GoUrl("-1","删除失败!");
            endif;
        }
    }
}