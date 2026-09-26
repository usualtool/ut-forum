<?php
require dirname(__FILE__).'/'.'power.php';
require dirname(__FILE__).'/'.'session.php';
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
if(!empty($_GET["fid"])){
    $app->Runin("fid",Inc::SqlCheck($_GET["fid"]));
}else{
    $app->Runin("fid",0);
}
$datatree=array();  
foreach(Data::QueryData("forum","","","forum_number desc","")["querydata"] as $value){  
    $datatree[$value['id']]=array(  
        'id'=>$value['id'],  
        'bid'=>$value['bid'],  
        'name'=>$value['forum_name']  
    );  
}
$app->Runin(array("webplace","datatree"),array("撰写主题",$datatree));
$app->Open("my_post_creat.cms");
if($_GET["do"]=="creat"){
    if(empty($_POST["ut-editor"])):
        Inc::GoUrl("-1","内容不能为空!"); 
    endif;
    $fid=Inc::SqlCheck($_POST["fid"]);
    if(!empty($_POST["notice"])):
        $notice=Inc::SqlCheck($_POST["notice"]);
    else:
        $notice=0;
    endif;
    $look=Inc::SqlCheck($_POST["look"]);
    $title=Inc::SqlCheck($_POST["title"]);
    $content=Inc::SqlCheck($_POST["ut-editor"]);
    $payfiles=Inc::SqlCheck($_POST["payfiles"]);
    $files=implode(",",Inc::SqlChecks($_POST["files"]));
            $pid=Data::InsertData("forum_post",array(
            "uid"=>$uid,
            "fid"=>$fid,
            "notice"=>$notice,
            "look"=>$look,
            "title"=>$title,
            "content"=>$content,
            "payfiles"=>$payfiles,
            "files"=>$files,
            "ip"=>Inc::GetIp(),
            "posttime"=>date('Y-m-d H:i:s',time())));
            if($pid):
                Inc::GoUrl(Route::Link("forum","post","id=".$pid),"撰写成功!");    
            else:
                Inc::GoUrl("-1","撰写失败!"); 
            endif;
}