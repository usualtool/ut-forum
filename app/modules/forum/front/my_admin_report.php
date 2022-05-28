<?php
require dirname(__FILE__).'/'.'power.php';
require dirname(__FILE__).'/'.'session.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
use library\UsualToolRoute\UTRoute;
if($utype!=99999):
    UTInc::GoUrl("-1","权限不足!"); 
endif;
$do=$_GET["do"];
$pagelink="?m=".$m."&p=".$p;
$page=empty($_GET["page"]) ? 1 : $_GET["page"];
$pagenum=10;
$minid=$pagenum*($page-1);
$data=UTData::QueryData("forum_report","","","addtime desc","$minid,$pagenum");
$querynum=$data["querynum"];
$querydata=$data["querydata"];
$totalpage=ceil($querynum/$pagenum);
$app->Runin("data",$querydata);
$app->Runin(array("webplace","total","curpage","listnum","pagelink"),array("举报列表",$totalpage,$page,$pagenum,$pagelink));
$app->Open("my_admin_report.cms");
if($do=="del"){
    $id=UTInc::SqlCheck($_GET["id"]);
    if(UTData::DelData("forum_report","id='$id'")){
        UTInc::GoUrl(UTRoute::Link("forum","my_admin_report"),"删除成功!");
    }else{
        UTInc::GoUrl("-1","删除失败!");
    }
}
if($do=="del_post"){
    $id=UTInc::SqlCheck($_GET["id"]);
    $postid=UTInc::SqlCheck($_GET["postid"]);
    if(UTData::DelData("forum_post","id='$postid'")){
        UTData::DelData("forum_report","id='$id'");
        UTInc::GoUrl(UTRoute::Link("forum","my_admin_report"),"删除成功!");
    }else{
        UTInc::GoUrl("-1","删除失败!");
    }
}
if($do=="del_reply"){
    $id=UTInc::SqlCheck($_GET["id"]);
    $replyid=UTInc::SqlCheck($_GET["replyid"]);
    if(UTData::DelData("forum_reply","id='$replyid'")){
        UTData::DelData("forum_report","id='$id'");
        UTInc::GoUrl(UTRoute::Link("forum","my_admin_report"),"删除成功!");
    }else{
        UTInc::GoUrl("-1","删除失败!");
    }
}