<?php
require dirname(__FILE__).'/'.'power.php';
require dirname(__FILE__).'/'.'session.php';
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
if(!empty($_GET["reveuid"])){
    $revid=Inc::SqlCheck($_GET["reveuid"]);
    if(Data::QueryData("forum_member","id","id='$revid'","","")["querynum"]==0):
        Inc::GoUrl("-1","接收账户不存在!"); 
    else:
        $app->Runin("reveuid",Data::QueryData("forum_member","username","id='$revid'","","")["querydata"][0]["username"]);
    endif;
}else{
    $app->Runin("reveuid","");
}
$app->Runin("webplace","发信");
$app->Open("my_message.cms");
if($_GET["do"]=="creat"){
    $reve=Inc::SqlCheck($_POST["reve"]);
    $content=Inc::SqlCheck($_POST["ut-editor"]);
        if(Data::QueryData("forum_member","","username='$reve'","","")["querynum"]==0):
            Inc::GoUrl("-1","接收方账户不存在!");
        else:
            $reveuid=Data::QueryData("forum_member","id","username='$reve'","","")["querydata"][0]["id"];
        endif;
            $pid=Data::InsertData("forum_message",array(
            "senduid"=>$uid,
            "reveuid"=>$reveuid,
            "content"=>$content,
            "sendtime"=>date('Y-m-d H:i:s',time())));
            if($pid):
                Inc::GoUrl(Route::Link("forum","my_sendbox"),"发信成功!");    
            else:
                Inc::GoUrl("-1","发信失败!"); 
            endif;
}