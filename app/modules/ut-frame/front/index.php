<?php
namespace Controller\Ut_frame\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
use usualtool\Lib\Cli;
use Model\Forum\Forum as ForumModel;
use Model\Forum\Session as SessionModel;
class Index{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $app,$config;
        if(!is_dir(OPEN_ROOT."/install") || file_exists(OPEN_ROOT."/install/usualtool.lock")):
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
            $page=empty($_GET["page"]) ? 1 : max(1,intval($_GET["page"]));
            $pagenum=10;
            $forum_post=$model->GetPosts($page,$pagenum);
            $querynum=$forum_post["querynum"];
            $querydata=$forum_post["querydata"];
            $notice=$this->Enrich($notice);
            $querydata=$this->Enrich($querydata);
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
            Inc::GoUrl("./install/");
        endif;
    }
    private function Enrich($rows){
        if(empty($rows) || !is_array($rows)){
            return $rows;
        }
        $uids=array();
        $fids=array();
        $pids=array();
        foreach($rows as $row){
            if(!empty($row["uid"])){
                $uids[intval($row["uid"])]=intval($row["uid"]);
            }
            if(!empty($row["fid"])){
                $fids[intval($row["fid"])]=intval($row["fid"]);
            }
            if(!empty($row["id"])){
                $pids[intval($row["id"])]=intval($row["id"]);
            }
        }
        $members=array();
        if(!empty($uids)):
            $members=Data::QueryData(
                "forum_member","id,username,avatar","id in (".implode(",",array_fill(0,count($uids),"?")).")","","",array_values($uids)
            )["querydata"];
            $members=array_column($members,null,"id");
        endif;
        $forums=array();
        if(!empty($fids)):
            $forums=Data::QueryData(
                "forum","id,forum_name","id in (".implode(",",array_fill(0,count($fids),"?")).")","","",array_values($fids)
            )["querydata"];
            $forums=array_column($forums,"forum_name","id");
        endif;
        $replys=array();
        if(!empty($pids)):
            $replys=Data::RunYu(
                "select postid,count(id) as num from `forum_reply` where postid in (".implode(",",array_fill(0,count($pids),"?")).") group by postid",
                array_values($pids)
            )["querydata"] ?? array();
            $replys=array_column($replys,"num","postid");
        endif;
        foreach($rows as $k=>$row){
            $uid=intval($row["uid"] ?? 0);
            $fid=intval($row["fid"] ?? 0);
            $pid=intval($row["id"] ?? 0);
            $rows[$k]["username"]=$members[$uid]["username"] ?? "";
            $rows[$k]["avatar"]=$members[$uid]["avatar"] ?? "";
            $rows[$k]["forum_name"]=$forums[$fid] ?? "";
            $rows[$k]["replynum"]=intval($replys[$pid] ?? 0);
        }
        return $rows;
    }
}