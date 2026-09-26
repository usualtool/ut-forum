<?php
require dirname(__FILE__).'/'.'power.php';
require dirname(__FILE__).'/'.'session.php';
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
if($utype!=99999):
    Inc::GoUrl("-1","权限不足!"); 
endif;
$do=$_GET["do"];
$pagelink="?m=".$m."&p=".$p;
$page=empty($_GET["page"]) ? 1 : $_GET["page"];
$pagenum=10;
$minid=$pagenum*($page-1);
$data=Data::QueryData("forum_report","","","addtime desc","$minid,$pagenum");
$querynum=$data["querynum"];
$querydata=$data["querydata"];
$totalpage=ceil($querynum/$pagenum);
$app->Runin("data",$querydata);
$app->Runin(array("webplace","total","curpage","listnum","pagelink"),array("举报列表",$totalpage,$page,$pagenum,$pagelink));
$app->Open("my_admin_report.cms");
if($do=="del"){
    $id=Inc::SqlCheck($_GET["id"]);
    if(Data::DelData("forum_report","id='$id'")){
        Inc::GoUrl(Route::Link("forum","my_admin_report"),"删除成功!");
    }else{
        Inc::GoUrl("-1","删除失败!");
    }
}
if($do=="del_post"){
    $id=Inc::SqlCheck($_GET["id"]);
    $postid=Inc::SqlCheck($_GET["postid"]);
    if(Data::DelData("forum_post","id='$postid'")){
        Data::DelData("forum_report","id='$id'");
        Inc::GoUrl(Route::Link("forum","my_admin_report"),"删除成功!");
    }else{
        Inc::GoUrl("-1","删除失败!");
    }
}
if($do=="del_reply"){
    $id=Inc::SqlCheck($_GET["id"]);
    $replyid=Inc::SqlCheck($_GET["replyid"]);
    if(Data::DelData("forum_reply","id='$replyid'")){
        Data::DelData("forum_report","id='$id'");
        Inc::GoUrl(Route::Link("forum","my_admin_report"),"删除成功!");
    }else{
        Inc::GoUrl("-1","删除失败!");
    }
}