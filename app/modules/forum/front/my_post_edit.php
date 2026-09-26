<?php
require dirname(__FILE__).'/'.'power.php';
require dirname(__FILE__).'/'.'session.php';
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
$id=Inc::SqlCheck($_GET["id"]);
$datatree=array();  
foreach(Data::QueryData("forum","","","forum_number desc","")["querydata"] as $value){  
    $datatree[$value['id']]=array(  
        'id'=>$value['id'],  
        'bid'=>$value['bid'],  
        'name'=>$value['forum_name']  
    );  
}
$data=Data::QueryData("forum_post","","id='$id' and uid='$uid'","","")["querydata"];
$app->Runin(array("webplace","datatree","id","data"),array("编辑主题",$datatree,$id,$data));
if($_GET["do"]=="edit"){
    $look=Inc::SqlCheck($_POST["look"]);
    $fid=Inc::SqlCheck($_POST["fid"]);
    $title=Inc::SqlCheck($_POST["title"]);
    $content=Inc::SqlCheck($_POST["ut-editor"]);
    $payfiles=Inc::SqlCheck($_POST["payfiles"]);
    $files=implode(",",Inc::SqlChecks($_POST["files"]));
    if(Data::UpdateData("forum_post",array(
        "look"=>$look,
        "fid"=>$fid,
        "title"=>$title,
        "content"=>$content,
        "payfiles"=>$payfiles,
        "files"=>$files),"id='$id' and uid='$uid'")):
            Inc::GoUrl(Route::Link("forum","post","id=".$id),"编辑成功!");
        else:
            Inc::GoUrl("-1","编辑失败!");
        endif;
}
$app->Open("my_post_edit.cms");