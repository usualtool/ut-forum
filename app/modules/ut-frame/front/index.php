<?php
namespace Controller\Ut_frame\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Forum\Forum as ForumModel;
use Model\Forum\Session as SessionModel;
class Index{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $app;
        if(Inc::SearchFile(UTF_ROOT."/usualtool.lock")):
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
            $totalpage=ceil($querynum/$pagenum);
            $app->Runin("forum_post",$querydata);
            $app->Runin(
                array("total","curpage","listnum","pagelink"),
                array($totalpage,$page,$pagenum,$pagelink)
            );
            $app->Runin(
                array("forum_temp","webplace","forum","notice","users","newtopic","topstar"),
                array(APP_ROOT."/modules/forum/skin/front","首页",$forum,$notice,$users,$newtopic,$topstar)
            );
            $app->Open("index.cms");
        else:
            $v="";
            $do=Inc::SqlCheck($_GET["do"] ?? "");
            if($do=="db-save"):
                $info=file_get_contents(UTF_ROOT."/.ut.config");
                foreach($_POST as $k=>$v):
                    $info=preg_replace("/{$k}=(.*)/","{$k}={$v}",$info);
                endforeach;
                file_put_contents(UTF_ROOT."/.ut.config",$info);
                Inc::GoUrl(Route::Link("ut-frame","index","do=sql"),"数据库配置成功!");
            endif;
            $app->Runin(array("v","do"),array($v,$do));
            $app->Open("install.cms");
        endif;
    }
}