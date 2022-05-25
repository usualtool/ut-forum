<?php
require dirname(__FILE__).'/'.'session.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
use library\UsualToolRoute\UTRoute;
$posnum=empty($_GET["posnum"]) ? UTInc::SqlCheck($_POST["posnum"]) : UTInc::SqlCheck($_GET["posnum"]);
if(!empty($posnum)){
    $data=UTData::QueryData("cms_pay_log","","posnum='$posnum'","","")["querydata"][0];
    $uid=$data["uid"];
    //社区整合数据
    $state=UTData::QueryData("forum_payment","state","posnum='$posnum'","","")["querydata"][0]["state"];
    $amount=$data["amount"]*$set[0]["rate"];
    $money=UTData::QueryData("forum_member","money","id='$uid'","","")["querydata"][0]["money"];
    $newmoney=round($money,2)+round($amount,2);
}
if(!empty($_GET["posnum"])){
    if($data["state"]==1 && $state==0){
        UTData::UpdateData("forum_member",array("money"=>$newmoney),"id='$uid'");
        UTData::UpdateData("forum_payment",array("state"=>1),"posnum='$posnum'");
        UTInc::GoUrl(UTRoute::Link("forum","my"),"充值成功!");
    }else{
        UTInc::GoUrl("-1","充值失败!");
    }
}
if(!empty($_POST["posnum"])){
    if($data["state"]==1 && $state==0){
        UTData::UpdateData("forum_member",array("money"=>$newmoney),"id='$uid'");
        UTData::UpdateData("forum_payment",array("state"=>1),"posnum='$posnum'");
    }
}