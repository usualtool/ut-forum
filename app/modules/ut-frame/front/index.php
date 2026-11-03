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
            $do=Inc::SqlCheck($_GET["do"] ?? "");
            if($do=="db-save"):
                $info=file_get_contents(UTF_ROOT."/.ut.config");
                foreach($_POST as $k=>$v):
                    $info=preg_replace("/{$k}=(.*)/","{$k}={$v}",$info);
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
                $tid=Inc::SqlCheck($_POST["tid"]);
                Cli::Install(array("usualtool","install","template",$tid,"-1"));
                if(is_dir(APP_ROOT."/template/".$tid)):
                    if(Data::ModTable("cms_template")):
                        Data::UpdateData("cms_template",array("makefront"=>1),"tid='$tid'");
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
}