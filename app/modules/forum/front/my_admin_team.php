<?php
require dirname(__FILE__).'/'.'power.php';
require dirname(__FILE__).'/'.'session.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
use library\UsualToolRoute\UTRoute;
if($utype!=99999):
    UTInc::GoUrl("-1","权限不足!"); 
endif;
$datatree=array();  
foreach(UTData::QueryData("forum","","","forum_number desc","")["querydata"] as $value){  
    $datatree[$value['id']]=array(  
        'id'=>$value['id'],  
        'bid'=>$value['bid'],  
        'name'=>$value['forum_name']  
    );  
}
$pagelink="?m=".$m."&p=".$p;
$page=empty($_GET["page"]) ? 1 : $_GET["page"];
$pagenum=5;
$minid=$pagenum*($page-1);
$data=UTData::QueryData("forum_member","","","utype desc,creattime desc","$minid,$pagenum");
$querynum=$data["querynum"];
$querydata=$data["querydata"];
$totalpage=ceil($querynum/$pagenum);
$app->Runin("data",$querydata);
$app->Runin(array("webplace","total","curpage","listnum","pagelink","datatree"),array("用户管理",$totalpage,$page,$pagenum,$pagelink,$datatree));
$app->Open("my_admin_team.cms");
if($_GET["do"]=="banzhu"){
    $id=UTInc::SqlCheck($_POST["id"]);
    $utype=UTInc::SqlCheck($_POST["utype"]);
            if(UTData::UpdateData("forum_member",array("utype"=>$utype),"id='$id'")):
                UTInc::GoUrl(UTRoute::Link("forum","my_admin_team"),"变更版主操作成功!");    
            else:
                UTInc::GoUrl("-1","变更版主操作失败!"); 
            endif;
}
if($_GET["do"]=="admin"){
    $id=UTInc::SqlCheck($_GET["id"]);
    $utype=UTInc::SqlCheck($_GET["utype"]);
    if($utype==0):
        if(UTData::QueryData("forum_member","","utype='99999'","","")["querynum"]==1):
            UTInc::GoUrl("-1","最后一个管理不能取消!");
        endif;
    endif;
            if(UTData::UpdateData("forum_member",array("utype"=>$utype),"id='$id'")):
                UTInc::GoUrl(UTRoute::Link("forum","my_admin_team"),"变更管理操作成功!");    
            else:
                UTInc::GoUrl("-1","变更管理操作失败!"); 
            endif;
}
if($_GET["do"]=="del"){
    $id=UTInc::SqlCheck($_GET["id"]);
        if(UTData::DelData("forum_member","id='$id'")):
            UTInc::GoUrl(UTRoute::Link("forum","my_admin_team"),"删除成功!");    
        else:
            UTInc::GoUrl("-1","删除失败!"); 
        endif;
}