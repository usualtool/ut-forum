<?php
require dirname(__FILE__).'/'.'power.php';
require dirname(__FILE__).'/'.'session.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
use library\UsualToolRoute\UTRoute;
$app->Runin("webplace","锁定/解除限制");
$app->Open("my_black.cms");
if($_GET["do"]=="black"){
    $black=UTInc::SqlCheck($_POST["black"]);
    $username=UTInc::SqlCheck($_POST["username"]);
        if(UTData::QueryData("forum_member","","username='$username'","","")["querynum"]==0):
            UTInc::GoUrl("-1","操作账户不存在!");
        else:
            if(UTData::UpdateData("forum_member",array("black"=>$black),"username='$username'")):
                UTInc::GoUrl(UTRoute::Link("forum","my_black"),"操作成功!");    
            else:
                UTInc::GoUrl("-1","操作失败!"); 
            endif;
        endif;
}