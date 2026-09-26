<?php
require dirname(__FILE__).'/'.'session.php';
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
$id=Inc::SqlCheck($_GET["id"]);
$rid=Inc::SqlCheck($_GET["rid"]);
$lookuid=Inc::SqlCheck($_GET["uid"]);
if(!empty($uid)):
    $my_rnum=Data::QueryData("forum_reply","","postid='$id' and uid='$uid'","","")["querynum"];
    $my_pnum=Data::QueryData("forum_post","","id='$id' and uid='$uid'","","")["querynum"];
    $my_replynum=$my_rnum+$my_pnum;
else:
    $my_replynum=0;
endif;
Data::UpdateData("forum_post",array("hit"=>"hit+1"),"id='$id'");
$data=Data::QueryData("forum_post","","id='$id'","","")["querydata"];
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
$data_reply=Data::QueryData("forum_reply","",$where,"replytime asc","$minid,$pagenum");
$querynum=$data_reply["querynum"];
$querydata=$data_reply["querydata"];
$totalpage=ceil($querynum/$pagenum);
$app->Runin("data_reply",$querydata);
$app->Runin(array("total","curpage","listnum","pagelink"),array($totalpage,$page,$pagenum,$pagelink));
$app->Runin(array("webplace","data","rid","my_replynum"),array($title,$data,$rid,$my_replynum));
$app->Open("post.cms");
if($_GET["do"]=="reply"){
    if(empty($_POST["ut-editor"])):
        Inc::GoUrl("-1","内容不能为空!"); 
    endif;
    $page=Inc::SqlCheck($_POST["page"]);
    $postid=Inc::SqlCheck($_POST["postid"]);
    $replyid=Inc::SqlCheck($_POST["replyid"]);
    $content=Inc::SqlCheck($_POST["ut-editor"]);
    $rid=Data::InsertData("forum_reply",array(
            "uid"=>$uid,
            "postid"=>$postid,
            "replyid"=>$replyid,
            "content"=>$content,
            "ip"=>Inc::GetIp(),
            "replytime"=>date('Y-m-d H:i:s',time())));
            if($rid):
                Inc::GoUrl(Route::Link("forum","post","id=".$postid."&page=".$page)."#end","回复主题成功!");
            else:
                Inc::GoUrl("-1","回复主题失败!"); 
            endif;
}
if($_GET["do"]=="payfiles"){
    $postmoney=$data[0]["payfiles"];
    $postuid=$data[0]["uid"];
    if(round($money,2)<round($postmoney,2)):
        Inc::GoUrl("-1","积分不足!"); 
    else:
        //1充值、2收入、3支出
        //用户支出
        $jianmoney=round($money,2)-round($postmoney,2);
        Data::InsertData("forum_payment",array(
            "types"=>3,
            "state"=>1,
            "posnum"=>Inc::GetRandomString(14,"123456789"),
            "uid"=>$uid,
            "pid"=>$id,
            "amount"=>$postmoney,
            "addtime"=>date('Y-m-d H:i:s',time())));
        if(Data::UpdateData("forum_member",array("money"=>$jianmoney),"id='$uid'")):
            //题主收入
            $postuidmoney=Data::QueryData("forum_member","money","id='$postuid'","","")["querydata"][0]["money"];
            $jiamoney=round($postuidmoney,2)+round($postmoney,2);
            Data::InsertData("forum_payment",array(
                "types"=>2,
                "state"=>1,
                "posnum"=>Inc::GetRandomString(14,"987654321"),
                "uid"=>$postuid,
                "pid"=>$id,
                "amount"=>$postmoney,
                "addtime"=>date('Y-m-d H:i:s',time())));
            Data::UpdateData("forum_member",array("money"=>$jiamoney),"id='$postuid'");
        endif;
        Inc::GoUrl(Route::Link("forum","post","id=".$id),"购买附件成功!"); 
     endif;
}
if($_GET["do"]=="file_comment"){
    $content=Inc::SqlCheck($_POST["file_content"]);
    if(empty($content)):
        Inc::GoUrl("-1","评语不能为空!"); 
    else:
        Data::InsertData("forum_comment",array(
            "types"=>2,
            "postid"=>$id,
            "uid"=>$uid,
            "content"=>$content,
            "addtime"=>date('Y-m-d H:i:s',time())));
        Inc::GoUrl("?m=forum&p=post&id=".$id."&page=1#filecomment","附件评价成功!"); 
    endif;
}
if($_GET["do"]=="report"){
    $ptype=Inc::SqlCheck($_POST["ptype"]);
    $postid=Inc::SqlCheck($_POST["postid"]);
    $content=Inc::SqlCheck($_POST["file_content"]);
    if(Data::InsertData("forum_report",array(
        "ptype"=>$ptype,
        "postid"=>$postid,
        "uid"=>$uid,
        "content"=>$content,
        "addtime"=>date('Y-m-d H:i:s',time())))):
        Inc::GoUrl("-1","举报完毕!");
    else:
        Inc::GoUrl("-1","举报失败!");
    endif;
}