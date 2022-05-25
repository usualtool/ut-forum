<?php
require dirname(__FILE__).'/'.'power.php';
require dirname(__FILE__).'/'.'session.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
use library\UsualToolRoute\UTRoute;
$id=UTInc::SqlCheck($_GET["id"]);
$datatree=array();  
foreach(UTData::QueryData("forum","","","forum_number desc","")["querydata"] as $value){  
    $datatree[$value['id']]=array(  
        'id'=>$value['id'],  
        'bid'=>$value['bid'],  
        'name'=>$value['forum_name']  
    );  
}
$data=UTData::QueryData("forum_post","","id='$id' and uid='$uid'","","")["querydata"];
$app->Runin(array("webplace","datatree","id","data"),array("编辑主题",$datatree,$id,$data));
if($_GET["do"]=="edit"){
    $look=UTInc::SqlCheck($_POST["look"]);
    $fid=UTInc::SqlCheck($_POST["fid"]);
    $title=UTInc::SqlCheck($_POST["title"]);
    $content=UTInc::SqlCheck($_POST["ut-editor"]);
    $payfiles=UTInc::SqlCheck($_POST["payfiles"]);
    $files=implode(",",UTInc::SqlChecks($_POST["files"]));
    if(UTData::UpdateData("forum_post",array(
        "look"=>$look,
        "fid"=>$fid,
        "title"=>$title,
        "content"=>$content,
        "payfiles"=>$payfiles,
        "files"=>$files),"id='$id' and uid='$uid'")):
            UTInc::GoUrl(UTRoute::Link("forum","post","id=".$id),"编辑成功!");
        else:
            UTInc::GoUrl("-1","编辑失败!");
        endif;
}
$app->Open("my_post_edit.cms");