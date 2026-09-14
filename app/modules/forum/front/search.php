<?php
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
use library\UsualToolPage\UTPage;
$key=UTInc::SqlCheck(urldecode($_GET["key"]));
$tags=UTData::QueryData("cms_search","hit,keyword","","hit desc","20")["querydata"];
$pagelink="?m=forum&p=search&key=".$key;
$page=empty($_GET["page"]) ? 1 : $_GET["page"];
$pagenum=10;
$minid=$pagenum*($page-1);
$data=UTData::QueryData("forum_post","","title like '%$key%' or content like '%$key%'","posttime desc","$minid,$pagenum");
$querynum=$data["querynum"];
if($querynum==0):
    UTInc::GoUrl("-1","没有搜索到有价值的信息!");
else:
    echo'<script>$(function(){$("title").html("搜索:'.$key.'")})</script>';
endif;
$querydata=$data["querydata"];
$totalpage=ceil($querynum/$pagenum);
$app->Runin(array("total","curpage","listnum","pagelink"),array($totalpage,$page,$pagenum,$pagelink));
$app->Runin(array("webplace","key","data","tags"),array("搜索:".$key,$key,$querydata,$tags));
$app->Open("search.cms");