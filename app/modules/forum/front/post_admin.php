<?php
require dirname(__FILE__).'/'.'power.php';
require dirname(__FILE__).'/'.'session.php';
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
$id=Inc::SqlCheck($_GET["id"]);
$rid=Inc::SqlCheck($_GET["rid"]);
$fid=Data::QueryData("forum_post","fid","id='$id'","","")["querydata"][0]["fid"];
if($utype==99999 || $utype==$fid){
    if($_GET["do"]=="ding"){
        $ding=Inc::SqlCheck($_GET["ding"]);
            if(Data::UpdateData("forum_post",array("ding"=>$ding),"id='$id'")):
                Inc::GoUrl(Route::Link("forum","post","id=".$id),"置顶状态操作成功!");    
            else:
                Inc::GoUrl("-1","置顶状态操作失败!"); 
            endif;
    }
    if($_GET["do"]=="close"){
        $close=Inc::SqlCheck($_GET["close"]);
            if(Data::UpdateData("forum_post",array("close"=>$close),"id='$id'")):
                Inc::GoUrl(Route::Link("forum","post","id=".$id),"屏蔽状态操作成功!");    
            else:
                Inc::GoUrl("-1","屏蔽状态操作失败!"); 
            endif;
    }
    if($_GET["do"]=="notice"){
            if(Data::UpdateData("forum_post",array("notice"=>0),"id='$id'")):
                Inc::GoUrl(Route::Link("forum","post","id=".$id),"解除公告成功!");    
            else:
                Inc::GoUrl("-1","解除公告失败!"); 
            endif;
    }
    if($_GET["do"]=="del"){
            if(Data::DelData("forum_post","id='$id'")):
                Inc::GoUrl(Route::Link("forum"),"主题删除成功!");    
            else:
                Inc::GoUrl("-1","主题删除失败!"); 
            endif;
    }
    if($_GET["do"]=="delreply"){
            if(Data::DelData("forum_reply","id='$rid'")):
                Inc::GoUrl(Route::Link("forum","post","id=".$id),"回复删除成功!");    
            else:
                Inc::GoUrl("-1","回复删除失败!"); 
            endif;
    }
}else{
    Inc::GoUrl("-1","权限不足!");
}