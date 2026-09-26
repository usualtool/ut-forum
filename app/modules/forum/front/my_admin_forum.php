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
$datatree=array();  
foreach(Data::QueryData("forum","","","forum_number desc","")["querydata"] as $value){  
    $datatree[$value['id']]=array(  
        'id'=>$value['id'],  
        'bid'=>$value['bid'],  
        'name'=>$value['forum_name']  
    );  
}
if($do=="mon"){
    $id=Inc::SqlCheck($_GET["id"]);
    $app->Runin("id",$id);
    $app->Runin("data",Data::QueryData("forum","","id='$id'","","")["querydata"]);
}
$app->Runin(array("webplace","do","datatree"),array("版块管理",$do,$datatree));
$app->Open("my_admin_forum.cms");
if($do=="del"){
    $id=Inc::SqlCheck($_GET["id"]);
    if(Data::DelData("forum","id='$id'")){
        Inc::GoUrl(Route::Link("forum","my_admin_forum"),"版块删除成功!");
    }else{
        Inc::GoUrl("-1","版块删除失败!");
    }
}
if($do=="a"){
    if(Data::InsertData("forum",array(
        "bid"=>Inc::SqlCheck($_POST["bid"]),
        "forum_name"=>Inc::SqlCheck($_POST["forum_name"]),
        "forum_number"=>Inc::SqlCheck($_POST["forum_number"]),
        "forum_content"=>Inc::SqlCheck($_POST["forum_content"])))){
        Inc::GoUrl(Route::Link("forum","my_admin_forum"),"版块创建成功!");
    }else{
        Inc::GoUrl("-1","版块创建失败!");
    }
}
if($do=="m"){
    $id=Inc::SqlCheck($_POST["id"]);
    if(Data::UpdateData("forum",array(
        "bid"=>Inc::SqlCheck($_POST["bid"]),
        "forum_name"=>Inc::SqlCheck($_POST["forum_name"]),
        "forum_number"=>Inc::SqlCheck($_POST["forum_number"]),
        "forum_content"=>Inc::SqlCheck($_POST["forum_content"])),"id='$id'")){
        Inc::GoUrl(Route::Link("forum","my_admin_forum"),"版块编辑成功!");
    }else{
        Inc::GoUrl("-1","版块编辑失败!");
    }
}