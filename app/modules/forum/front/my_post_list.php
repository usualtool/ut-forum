<?php
require dirname(__FILE__).'/'.'power.php';
require dirname(__FILE__).'/'.'session.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
use library\UsualToolRoute\UTRoute;
$pagelink="?m=".$m."&p=".$p;
$page=empty($_GET["page"]) ? 1 : $_GET["page"];
$pagenum=10;
$minid=$pagenum*($page-1);
$data=UTData::QueryData("forum_post","","uid='$uid'","posttime desc","$minid,$pagenum");
$querynum=$data["querynum"];
$querydata=$data["querydata"];
$totalpage=ceil($querynum/$pagenum);
$app->Runin("data",$querydata);
$app->Runin(array("webplace","total","curpage","listnum","pagelink"),array("主题列表",$totalpage,$page,$pagenum,$pagelink));
$app->Open("my_post_list.cms");
if($_GET["do"]=="del"){
    $id=UTInc::SqlCheck($_GET["id"]);
    if(UTData::DelData("forum_post","uid='$uid' and id='$id'")):
        UTInc::GoUrl(UTRoute::Link("forum","my_post_list"),"删除成功!");
    else:
        UTInc::GoUrl("-1","删除失败!");
    endif;
}