<?php
require dirname(__FILE__).'/'.'power.php';
require dirname(__FILE__).'/'.'session.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
use library\UsualToolRoute\UTRoute;
if(!empty($_GET["reveuid"])){
    $revid=UTInc::SqlCheck($_GET["reveuid"]);
    if(UTData::QueryData("forum_member","id","id='$revid'","","")["querynum"]==0):
        UTInc::GoUrl("-1","接收账户不存在!"); 
    else:
        $app->Runin("reveuid",UTData::QueryData("forum_member","username","id='$revid'","","")["querydata"][0]["username"]);
    endif;
}else{
    $app->Runin("reveuid","");
}
$app->Runin("webplace","发信");
$app->Open("my_message.cms");
if($_GET["do"]=="creat"){
    $reve=UTInc::SqlCheck($_POST["reve"]);
    $content=UTInc::SqlCheck($_POST["ut-editor"]);
        if(UTData::QueryData("forum_member","","username='$reve'","","")["querynum"]==0):
            UTInc::GoUrl("-1","接收方账户不存在!");
        else:
            $reveuid=UTData::QueryData("forum_member","id","username='$reve'","","")["querydata"][0]["id"];
        endif;
            $pid=UTData::InsertData("forum_message",array(
            "senduid"=>$uid,
            "reveuid"=>$reveuid,
            "content"=>$content,
            "sendtime"=>date('Y-m-d H:i:s',time())));
            if($pid):
                UTInc::GoUrl(UTRoute::Link("forum","my_sendbox"),"发信成功!");    
            else:
                UTInc::GoUrl("-1","发信失败!"); 
            endif;
}