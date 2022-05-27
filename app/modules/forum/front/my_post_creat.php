<?php
require dirname(__FILE__).'/'.'power.php';
require dirname(__FILE__).'/'.'session.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
use library\UsualToolRoute\UTRoute;
if(!empty($_GET["fid"])){
    $app->Runin("fid",UTInc::SqlCheck($_GET["fid"]));
}else{
    $app->Runin("fid",0);
}
$datatree=array();  
foreach(UTData::QueryData("forum","","","forum_number desc","")["querydata"] as $value){  
    $datatree[$value['id']]=array(  
        'id'=>$value['id'],  
        'bid'=>$value['bid'],  
        'name'=>$value['forum_name']  
    );  
}
$app->Runin(array("webplace","datatree"),array("撰写主题",$datatree));
$app->Open("my_post_creat.cms");
if($_GET["do"]=="creat"){
    $fid=UTInc::SqlCheck($_POST["fid"]);
    if(!empty($_POST["notice"])):
        $notice=UTInc::SqlCheck($_POST["notice"]);
    else:
        $notice=0;
    endif;
    $look=UTInc::SqlCheck($_POST["look"]);
    $title=UTInc::SqlCheck($_POST["title"]);
    $content=UTInc::SqlCheck($_POST["ut-editor"]);
    $payfiles=UTInc::SqlCheck($_POST["payfiles"]);
    $files=implode(",",UTInc::SqlChecks($_POST["files"]));
            $pid=UTData::InsertData("forum_post",array(
            "uid"=>$uid,
            "fid"=>$fid,
            "notice"=>$notice,
            "look"=>$look,
            "title"=>$title,
            "content"=>$content,
            "payfiles"=>$payfiles,
            "files"=>$files,
            "ip"=>UTInc::GetIp(),
            "posttime"=>date('Y-m-d H:i:s',time())));
            if($pid):
                UTInc::GoUrl(UTRoute::Link("forum","post","id=".$pid),"撰写成功!");    
            else:
                UTInc::GoUrl("-1","撰写失败!"); 
            endif;
}