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
$datatree=array();  
foreach(UTData::QueryData("forum","","","forum_number desc","")["querydata"] as $value){  
    $datatree[$value['id']]=array(  
        'id'=>$value['id'],  
        'bid'=>$value['bid'],  
        'name'=>$value['forum_name']  
    );  
}
if($do=="mon"){
    $id=UTInc::SqlCheck($_GET["id"]);
    $app->Runin("id",$id);
    $app->Runin("data",UTData::QueryData("forum","","id='$id'","","")["querydata"]);
}
$app->Runin(array("webplace","do","datatree"),array("版块管理",$do,$datatree));
$app->Open("my_admin_forum.cms");
if($do=="del"){
    $id=UTInc::SqlCheck($_GET["id"]);
    if(UTData::DelData("forum","id='$id'")){
        UTInc::GoUrl(UTRoute::Link("forum","my_admin_forum"),"版块删除成功!");
    }else{
        UTInc::GoUrl("-1","版块删除失败!");
    }
}
if($do=="a"){
    if(UTData::InsertData("forum",array(
        "bid"=>UTInc::SqlCheck($_POST["bid"]),
        "forum_name"=>UTInc::SqlCheck($_POST["forum_name"]),
        "forum_number"=>UTInc::SqlCheck($_POST["forum_number"]),
        "forum_content"=>UTInc::SqlCheck($_POST["forum_content"])))){
        UTInc::GoUrl(UTRoute::Link("forum","my_admin_forum"),"版块创建成功!");
    }else{
        UTInc::GoUrl("-1","版块创建失败!");
    }
}
if($do=="m"){
    $id=UTInc::SqlCheck($_POST["id"]);
    if(UTData::UpdateData("forum",array(
        "bid"=>UTInc::SqlCheck($_POST["bid"]),
        "forum_name"=>UTInc::SqlCheck($_POST["forum_name"]),
        "forum_number"=>UTInc::SqlCheck($_POST["forum_number"]),
        "forum_content"=>UTInc::SqlCheck($_POST["forum_content"])),"id='$id'")){
        UTInc::GoUrl(UTRoute::Link("forum","my_admin_forum"),"版块编辑成功!");
    }else{
        UTInc::GoUrl("-1","版块编辑失败!");
    }
}