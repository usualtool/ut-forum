<?php
require dirname(__FILE__).'/'.'power.php';
require dirname(__FILE__).'/'.'session.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
if($utype!=99999):
    UTInc::GoUrl("-1","权限不足!"); 
endif;
$pagelink="?m=".$m."&p=".$p;
$page=empty($_GET["page"]) ? 1 : $_GET["page"];
$pagenum=10;
$minid=$pagenum*($page-1);
$data=UTData::QueryData("forum_post","","close=1","posttime desc","$minid,$pagenum");
$querynum=$data["querynum"];
$querydata=$data["querydata"];
$totalpage=ceil($querynum/$pagenum);
$app->Runin("data",$querydata);
$app->Runin(array("webplace","total","curpage","listnum","pagelink"),array("屏蔽管理",$totalpage,$page,$pagenum,$pagelink));
$app->Open("my_admin_black.cms");