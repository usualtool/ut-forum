<?php
require dirname(__FILE__).'/'.'session.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
use library\UsualToolRoute\UTRoute;
$id=UTInc::SqlCheck($_GET["id"]);
$rid=UTInc::SqlCheck($_GET["rid"]);
$lookuid=UTInc::SqlCheck($_GET["uid"]);
if(!empty($uid)):
    $my_rnum=UTData::QueryData("forum_reply","","postid='$id' and uid='$uid'","","")["querynum"];
    $my_pnum=UTData::QueryData("forum_post","","id='$id' and uid='$uid'","","")["querynum"];
    $my_replynum=$my_rnum+$my_pnum;
else:
    $my_replynum=0;
endif;
UTData::UpdateData("forum_post",array("hit"=>"hit+1"),"id='$id'");
$data=UTData::QueryData("forum_post","","id='$id'","","")["querydata"];
if($data[0]["close"]==1){
    $title="主题已被屏蔽";
}else{
    $title=$data[0]["title"];
}
if(!empty($lookuid)):
    $pagelink="?m=".$m."&p=".$p."&id=".$id."&uid=".$lookuid;
    $where="postid='$id' and uid='$lookuid'";
else:
    $pagelink="?m=".$m."&p=".$p."&id=".$id;
    $where="postid='$id'";
endif;
$page=empty($_GET["page"]) ? 1 : $_GET["page"];
$pagenum=5;
$minid=$pagenum*($page-1);
$data_reply=UTData::QueryData("forum_reply","",$where,"replytime asc","$minid,$pagenum");
$querynum=$data_reply["querynum"];
$querydata=$data_reply["querydata"];
$totalpage=ceil($querynum/$pagenum);
$app->Runin("data_reply",$querydata);
$app->Runin(array("total","curpage","listnum","pagelink"),array($totalpage,$page,$pagenum,$pagelink));
$app->Runin(array("webplace","data","rid","my_replynum"),array($title,$data,$rid,$my_replynum));
$app->Open("post.cms");
if($_GET["do"]=="reply"){
    $page=UTInc::SqlCheck($_POST["page"]);
    $postid=UTInc::SqlCheck($_POST["postid"]);
    $replyid=UTInc::SqlCheck($_POST["replyid"]);
    $content=UTInc::SqlCheck($_POST["ut-editor"]);
    $rid=UTData::InsertData("forum_reply",array(
            "uid"=>$uid,
            "postid"=>$postid,
            "replyid"=>$replyid,
            "content"=>$content,
            "ip"=>UTInc::GetIp(),
            "replytime"=>date('Y-m-d H:i:s',time())));
            if($rid):
                UTInc::GoUrl(UTRoute::Link("forum","post","id=".$postid."&page=".$page)."#end","回复主题成功!");
            else:
                UTInc::GoUrl("-1","回复主题失败!"); 
            endif;
}
if($_GET["do"]=="payfiles"){
    $postmoney=$data[0]["payfiles"];
    $postuid=$data[0]["uid"];
    if(round($money,2)<round($postmoney,2)):
        UTInc::GoUrl("-1","积分不足!"); 
    else:
        //1充值、2收入、3支出
        //用户支出
        $jianmoney=round($money,2)-round($postmoney,2);
        UTData::InsertData("forum_payment",array(
            "types"=>3,
            "state"=>1,
            "posnum"=>UTInc::GetRandomString(14,"123456789"),
            "uid"=>$uid,
            "pid"=>$id,
            "amount"=>$postmoney,
            "addtime"=>date('Y-m-d H:i:s',time())));
        if(UTData::UpdateData("forum_member",array("money"=>$jianmoney),"id='$uid'")):
            //题主收入
            $postuidmoney=UTData::QueryData("forum_member","money","id='$postuid'","","")["querydata"][0]["money"];
            $jiamoney=round($postuidmoney,2)+round($postmoney,2);
            UTData::InsertData("forum_payment",array(
                "types"=>2,
                "state"=>1,
                "posnum"=>UTInc::GetRandomString(14,"987654321"),
                "uid"=>$postuid,
                "pid"=>$id,
                "amount"=>$postmoney,
                "addtime"=>date('Y-m-d H:i:s',time())));
            UTData::UpdateData("forum_member",array("money"=>$jiamoney),"id='$postuid'");
        endif;
        UTInc::GoUrl(UTRoute::Link("forum","post","id=".$id),"购买附件成功!"); 
     endif;
}
if($_GET["do"]=="file_comment"){
    $content=UTInc::SqlCheck($_POST["file_content"]);
    if(empty($content)):
        UTInc::GoUrl("-1","评语不能为空!"); 
    else:
        UTData::InsertData("forum_comment",array(
            "types"=>2,
            "postid"=>$id,
            "uid"=>$uid,
            "content"=>$content,
            "addtime"=>date('Y-m-d H:i:s',time())));
        UTInc::GoUrl("?m=forum&p=post&id=".$id."&page=1#filecomment","附件评价成功!"); 
    endif;
}
if($_GET["do"]=="report"){
    $ptype=UTInc::SqlCheck($_POST["ptype"]);
    $postid=UTInc::SqlCheck($_POST["postid"]);
    $content=UTInc::SqlCheck($_POST["file_content"]);
    if(UTData::InsertData("forum_report",array(
        "ptype"=>$ptype,
        "postid"=>$postid,
        "uid"=>$uid,
        "content"=>$content,
        "addtime"=>date('Y-m-d H:i:s',time())))):
        UTInc::GoUrl("-1","举报完毕!");
    else:
        UTInc::GoUrl("-1","举报失败!");
    endif;
}