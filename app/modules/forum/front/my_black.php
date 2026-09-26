<?php
require dirname(__FILE__).'/'.'power.php';
require dirname(__FILE__).'/'.'session.php';
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
$app->Runin("webplace","锁定/解除限制");
$app->Open("my_black.cms");
if($_GET["do"]=="black"){
    $black=Inc::SqlCheck($_POST["black"]);
    $username=Inc::SqlCheck($_POST["username"]);
        if(Data::QueryData("forum_member","","username='$username'","","")["querynum"]==0):
            Inc::GoUrl("-1","操作账户不存在!");
        else:
            if(Data::UpdateData("forum_member",array("black"=>$black),"username='$username'")):
                Inc::GoUrl(Route::Link("forum","my_black"),"操作成功!");    
            else:
                Inc::GoUrl("-1","操作失败!"); 
            endif;
        endif;
}