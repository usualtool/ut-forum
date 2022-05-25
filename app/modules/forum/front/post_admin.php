<?php
require dirname(__FILE__).'/'.'power.php';
require dirname(__FILE__).'/'.'session.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
use library\UsualToolRoute\UTRoute;
$id=UTInc::SqlCheck($_GET["id"]);
$rid=UTInc::SqlCheck($_GET["rid"]);
$fid=UTData::QueryData("forum_post","fid","id='$id'","","")["querydata"][0]["fid"];
if($utype==99999 || $utype==$fid){
    if($_GET["do"]=="ding"){
        $ding=UTInc::SqlCheck($_GET["ding"]);
            if(UTData::UpdateData("forum_post",array("ding"=>$ding),"id='$id'")):
                UTInc::GoUrl(UTRoute::Link("forum","post","id=".$id),"置顶状态操作成功!");    
            else:
                UTInc::GoUrl("-1","置顶状态操作失败!"); 
            endif;
    }
    if($_GET["do"]=="close"){
        $close=UTInc::SqlCheck($_GET["close"]);
            if(UTData::UpdateData("forum_post",array("close"=>$close),"id='$id'")):
                UTInc::GoUrl(UTRoute::Link("forum","post","id=".$id),"屏蔽状态操作成功!");    
            else:
                UTInc::GoUrl("-1","屏蔽状态操作失败!"); 
            endif;
    }
    if($_GET["do"]=="notice"){
            if(UTData::UpdateData("forum_post",array("notice"=>0),"id='$id'")):
                UTInc::GoUrl(UTRoute::Link("forum","post","id=".$id),"解除公告成功!");    
            else:
                UTInc::GoUrl("-1","解除公告失败!"); 
            endif;
    }
    if($_GET["do"]=="del"){
            if(UTData::DelData("forum_post","id='$id'")):
                UTInc::GoUrl(UTRoute::Link("forum"),"主题删除成功!");    
            else:
                UTInc::GoUrl("-1","主题删除失败!"); 
            endif;
    }
    if($_GET["do"]=="delreply"){
            if(UTData::DelData("forum_reply","id='$rid'")):
                UTInc::GoUrl(UTRoute::Link("forum","post","id=".$id),"回复删除成功!");    
            else:
                UTInc::GoUrl("-1","回复删除失败!"); 
            endif;
    }
}else{
    UTInc::GoUrl("-1","权限不足!");
}