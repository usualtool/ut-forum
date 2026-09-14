<?php
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
use library\UsualToolRoute\UTRoute;
if(UTData::ModTable("forum") && UTInc::SearchFile(UTF_ROOT."/usualtool.lock")):
    require APP_ROOT."/modules/forum/front/session.php";
    $notice=UTData::QueryData("forum_post","","close=0 and notice=1","posttime desc")["querydata"];
    $forum=UTData::QueryData("forum","","bid=0","forum_number desc")["querydata"];
    $users=UTData::QueryData("forum_member","","","creattime desc","0,10")["querydata"];
    $newtopic=UTData::QueryData("forum_post","","close=0 and notice=0","posttime desc","0,5")["querydata"];
    $topstar=UTData::QueryData("forum_member","","id in (Select uid from forum_post group by uid order by count(id) desc)","","0,3")["querydata"];
    $pagelink="?m=".$m."&p=".$p;
    $page=empty($_GET["page"]) ? 1 : $_GET["page"];
        $pagenum=10;
    $minid=$pagenum*($page-1);
    $forum_post=UTData::QueryData(
        "forum_post",
        "",
        "close=0 and notice=0",
        "ding desc,posttime desc",
        "$minid,$pagenum"
    );
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
    $do=UTInc::SqlCheck($_GET["do"]);
    if($do=="db-save"):
       $info = file_get_contents(UTF_ROOT."/.ut.config"); 
       foreach($_POST as $k=>$v):
           $info = preg_replace("/{$k}=(.*)/","{$k}={$v}",$info); 
       endforeach;
       file_put_contents(UTF_ROOT."/.ut.config",$info);
       UTInc::GoUrl("?m=ut-frame&do=sql","数据库配置成功!");
    endif;
    $app->Runin(array("v","do"),array($v,$do));
    $app->Open("install.cms");
endif;