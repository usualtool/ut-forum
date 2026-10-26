<?php
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
$pagelink="";
$page=empty($_GET["page"]) ? 1 : $_GET["page"];
$pagenum=10;
$minid=$pagenum*($page-1);
$data=Data::QueryData("cms_pay_log","","","postime asc","$minid,$pagenum");
$querynum=$data["querynum"];
$querydata=$data["querydata"];
$totalpage=ceil($querynum/$pagenum);
$app->Runin(array("data","total","curpage","listnum","pagelink"),array($querydata,$totalpage,$page,$pagenum,$pagelink));
$app->Open("log.cms");
if($_GET["do"]=="del"){
    if(!empty($_GET["id"])):
        $ids=implode("','",Inc::SqlCheck($_GET["id"]));
        $result=Data::DelData("cms_pay_log","id in ('$ids')");
        if(!$result):
            Inc::GoUrl(Route::Link("payment","log"),"删除失败!");
        else:
            Inc::GoUrl(Route::Link("payment","log"),"删除成功!");
        endif;
    else:
        Inc::GoUrl(Route::Link("payment","log"),"选中项为空!");
    endif;
}