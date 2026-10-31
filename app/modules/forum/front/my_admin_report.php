<?php
namespace Controller\Forum\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Forum\Forum as ForumModel;
use Model\Forum\Session as SessionModel;
use Model\Forum\Power as PowerModel;
class My_admin_report{
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
        $do=$_GET["do"] ?? "";
        $pagelink="";

        $page=empty($_GET["page"]) ? 1 : $_GET["page"];
        $pagenum=10;
        $data=$model->GetReports($page,$pagenum);
        $querynum=$data["querynum"];
        $querydata=$data["querydata"];
        $totalpage=ceil($querynum/$pagenum);
        $app->Runin("data",$querydata);
        $app->Runin(array("webplace","total","curpage","listnum","pagelink"),array("举报列表",$totalpage,$page,$pagenum,$pagelink));
        $app->Open("my_admin_report.cms");
        if($do=="del"){
            $id=Inc::SqlCheck($_GET["id"] ?? "");
            if($model->DeleteReport($id)){
                Inc::GoUrl(Route::Link("forum","my_admin_report"),"删除成功!");
            }else{
                Inc::GoUrl("-1","删除失败!");
            }
        }
        if($do=="del_post"){
            $id=Inc::SqlCheck($_GET["id"] ?? "");
            $postid=Inc::SqlCheck($_GET["postid"] ?? "");
            if($model->DeletePost($postid)){
                $model->DeleteReport($id);
                Inc::GoUrl(Route::Link("forum","my_admin_report"),"删除成功!");
            }else{
                Inc::GoUrl("-1","删除失败!");
            }
        }
        if($do=="del_reply"){
            $id=Inc::SqlCheck($_GET["id"] ?? "");
            $replyid=Inc::SqlCheck($_GET["replyid"] ?? "");
            if($model->DeleteReply($replyid)){
                $model->DeleteReport($id);
                Inc::GoUrl(Route::Link("forum","my_admin_report"),"删除成功!");
            }else{
                Inc::GoUrl("-1","删除失败!");
            }
        }
    }
}