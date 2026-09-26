<?php
require dirname(__FILE__).'/'.'power.php';
require dirname(__FILE__).'/'.'session.php';
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
if($utype!=99999):
    Inc::GoUrl("-1","权限不足!"); 
endif;
$datatree=array();  
foreach(Data::QueryData("forum","","","forum_number desc","")["querydata"] as $value){  
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
$data=Data::QueryData("forum_member","","","utype desc,creattime desc","$minid,$pagenum");
$querynum=$data["querynum"];
$querydata=$data["querydata"];
$totalpage=ceil($querynum/$pagenum);
$app->Runin("data",$querydata);
$app->Runin(array("webplace","total","curpage","listnum","pagelink","datatree"),array("用户管理",$totalpage,$page,$pagenum,$pagelink,$datatree));
$app->Open("my_admin_team.cms");
if($_GET["do"]=="banzhu"){
    $id=Inc::SqlCheck($_POST["id"]);
    $utype=Inc::SqlCheck($_POST["utype"]);
            if(Data::UpdateData("forum_member",array("utype"=>$utype),"id='$id'")):
                Inc::GoUrl(Route::Link("forum","my_admin_team"),"变更版主操作成功!");    
            else:
                Inc::GoUrl("-1","变更版主操作失败!"); 
            endif;
}
if($_GET["do"]=="admin"){
    $id=Inc::SqlCheck($_GET["id"]);
    $utype=Inc::SqlCheck($_GET["utype"]);
    if($utype==0):
        if(Data::QueryData("forum_member","","utype='99999'","","")["querynum"]==1):
            Inc::GoUrl("-1","最后一个管理不能取消!");
        endif;
    endif;
            if(Data::UpdateData("forum_member",array("utype"=>$utype),"id='$id'")):
                Inc::GoUrl(Route::Link("forum","my_admin_team"),"变更管理操作成功!");    
            else:
                Inc::GoUrl("-1","变更管理操作失败!"); 
            endif;
}
if($_GET["do"]=="del"){
    $id=Inc::SqlCheck($_GET["id"]);
        if(Data::DelData("forum_member","id='$id'")):
            Inc::GoUrl(Route::Link("forum","my_admin_team"),"删除成功!");    
        else:
            Inc::GoUrl("-1","删除失败!"); 
        endif;
}