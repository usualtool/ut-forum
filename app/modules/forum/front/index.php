<?php
namespace Controller\Forum\Front;
use Model\Forum\Forum as ForumModel;
use Model\Forum\Session as SessionModel;
class Index{
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
        $notice=$model->GetNotice();
        $forum=$model->GetForums();
        $users=$model->GetNewUsers();
        $newtopic=$model->GetNewTopics();
        $topstar=$model->GetTopStars();
        $pagelink="";

        $page=empty($_GET["page"]) ? 1 : $_GET["page"];
        $pagenum=10;
        $forum_post=$model->GetPosts($page,$pagenum);
        $querynum=$forum_post["querynum"];
        $querydata=$forum_post["querydata"];
        $totalpage=$querynum==0 ? 1 : ceil($querynum/$pagenum);
        $app->Runin("forum_post",$querydata);
        $app->Runin(array("total","curpage","listnum","pagelink"),array($totalpage,$page,$pagenum,$pagelink));
        $app->Runin(array("webplace","forum","notice","users","newtopic","topstar"),array("首页",$forum,$notice,$users,$newtopic,$topstar));
        $app->Open("index.cms");
    }
}