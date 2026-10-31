<?php
namespace Controller\Forum\Front;
use usualtool\Lib\Inc;
use Model\Forum\Forum as ForumModel;
use Model\Forum\Session as SessionModel;
class Forum_list{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $app;
        $model=new ForumModel();
        $session=(new SessionModel())->Check();
        $app->Runin(
            array("rate","user","uid","utype","username","money","avatar"),
            array($session["rate"],$session["user"],$session["uid"],$session["utype"],$session["username"],$session["money"],$session["avatar"])
        );
        $id=Inc::SqlCheck($_GET["id"] ?? "");
        $forum_name=$model->GetForumName($id);
        $forum=$model->GetForums();
        $pagelink="?&id=".$id;

        $page=empty($_GET["page"]) ? 1 : $_GET["page"];
        $pagenum=10;
        $forum_post=$model->GetForumPosts($id,$page,$pagenum);
        $querynum=$forum_post["querynum"];
        $querydata=$forum_post["querydata"];
        $totalpage=$querynum==0 ? 1 : ceil($querynum/$pagenum);
        $app->Runin("forum_post",$querydata);
        $app->Runin(array("total","curpage","listnum","pagelink"),array($totalpage,$page,$pagenum,$pagelink));
        $app->Runin(array("webplace","id","forum"),array($forum_name,$id,$forum));
        $app->Open("list.cms");
    }
}