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
            $data=array();
            $temps=Inc::Auth("temp");
            preg_match_all( "/\<temp\>(.*?)\<\/temp\>/s",$temps,$tempblocks);
            foreach($tempblocks[1] as $temp):
                preg_match_all("/\<tid\>(.*?)\<\/tid\>/",$temp,$tid);
                preg_match_all("/\<title\>(.*?)\<\/title\>/",$temp,$title);
                preg_match_all("/\<picurl\>(.*?)\<\/picurl\>/",$temp,$picurl);
                preg_match_all("/\<isfree\>(.*?)\<\/isfree\>/",$temp,$isfree);
                if(substr($tid[1][0],0,2)=='f-' && strpos($isfree[1][0],'公开使用')!==false):
                    $data[]=array("tid"=>$tid[1][0],"title"=>$title[1][0],"picurl"=>$picurl[1][0],"isfree"=>$isfree[1][0]);
                endif;
            endforeach;
            $do=trim((string)($_GET["do"] ?? ""));
            if($do=="db-save"):
                $info=file_get_contents(UTF_ROOT."/.ut.config");
                foreach($_POST as $k=>$v):
                    if(!is_scalar($v) || !preg_match("/^[A-Za-z0-9_]+$/",(string)$k)):
                        continue;
                    endif;
                    $info=preg_replace_callback(
                        "/^".preg_quote((string)$k,"/")."=(.*?)(?=\r?$)/m",
                        function($m) use ($k,$v){ return $k."=".(string)$v; },
                        $info
                    );
                endforeach;
                file_put_contents(UTF_ROOT."/.ut.config",$info);
                Inc::GoUrl(Route::Link("ut-frame","index","do=sql"),"数据库配置成功!");
            elseif($do=="db-sql"):
                $sql=file_get_contents(UTF_ROOT."/forum.sql");
                $arr=explode(';',$sql);
                $total=count($arr)-1;
                for($i=0;$i<$total;$i++):
                    $one=trim($arr[$i]);
                    if($one===""){
                        continue;
                    }
                    Data::RunSql($one);
                endfor;
                Inc::GoUrl(Route::Link("ut-frame","index","do=temp"),"数据导入成功!");
            elseif($do=="db-temp"):
                $tid=trim((string)($_POST["tid"] ?? ""));
                if(!preg_match("/^[A-Za-z0-9_\-]+$/",$tid)):
                    Inc::GoUrl("-1","模板ID无效!");
                endif;
                Cli::Install(array("usualtool","install","template",$tid,"-1"));
                if(is_dir(APP_ROOT."/template/".$tid)):
                    if(Data::ModTable("cms_template")):
                        Data::UpdateData("cms_template",array("makefront"=>1),"tid=?",array($tid));
                    endif;
                    $info=preg_replace("/TEMPFRONT=(.*)/","TEMPFRONT={$tid}",file_get_contents(UTF_ROOT."/.ut.config"));
                    file_put_contents(UTF_ROOT."/.ut.config",$info);
                    file_put_contents(UTF_ROOT."/usualtool.lock","lock");
                    Inc::GoUrl(Route::Link("ut-frame","index","do=finish"),"安装模板成功!");
                else:
                    Inc::GoUrl("-1","安装模板失败!");
                endif;
            elseif($do=="finish"):
                file_put_contents(UTF_ROOT."/usualtool.lock","lock");
                Inc::GoUrl("./","UTForum安装成功!");
            endif;
            $app->Runin(
                array("v","do","config","data"),
                array($v,$do,$config,$data)
            );
            $app->Open("install.cms");
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