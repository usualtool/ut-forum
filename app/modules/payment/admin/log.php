<?php
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
$pagelink="?m=".$m."&p=".$p;
$page=empty($_GET["page"]) ? 1 : $_GET["page"];
$pagenum=10;
$minid=$pagenum*($page-1);
$data=UTData::QueryData("cms_pay_log","","","postime asc","$minid,$pagenum");
$querynum=$data["querynum"];
$querydata=$data["querydata"];
$totalpage=ceil($querynum/$pagenum);
$app->Runin(array("data","total","curpage","listnum","pagelink"),array($querydata,$totalpage,$page,$pagenum,$pagelink));
$app->Open("log.cms");
if($_GET["do"]=="del"){
    if(!empty($_GET["id"])):
        $ids=implode("','",UTInc::SqlChecks($_GET["id"]));
        $result=UTData::DelData("cms_pay_log","id in ('$ids')");
        if(!$result):
            UTInc::GoUrl("?m=payment&p=log","删除失败!");
        else:
            UTInc::GoUrl("?m=payment&p=log","删除成功!");
        endif;
    else:
        UTInc::GoUrl("?m=payment&p=log","选中项为空!");
    endif;
}