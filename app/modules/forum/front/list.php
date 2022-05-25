<?php
require dirname(__FILE__).'/'.'session.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
$id=UTInc::SqlCheck($_GET["id"]);
$forum_name=UTData::QueryData("forum","forum_name","id='$id'","","")["querydata"][0]["forum_name"];
$forum=UTData::QueryData("forum","","bid=0","forum_number desc","")["querydata"];
$pagelink="?m=".$m."&p=".$p."&id=".$id;
$page=empty($_GET["page"]) ? 1 : $_GET["page"];
$pagenum=10;
$minid=$pagenum*($page-1);
$forum_post=UTData::QueryData("forum_post","","fid='$id' and close=0 and notice=0","ding desc,posttime desc","$minid,$pagenum");
$querynum=$forum_post["querynum"];
$querydata=$forum_post["querydata"];
$totalpage=ceil($querynum/$pagenum);
$app->Runin("forum_post",$querydata);
$app->Runin(array("total","curpage","listnum","pagelink"),array($totalpage,$page,$pagenum,$pagelink));
$app->Runin(array("webplace","id","forum"),array($forum_name,$id,$forum));
$app->Open("list.cms");