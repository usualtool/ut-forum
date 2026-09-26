<?php
require dirname(__FILE__).'/'.'session.php';
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
$posnum=empty($_GET["posnum"]) ? Inc::SqlCheck($_POST["posnum"]) : Inc::SqlCheck($_GET["posnum"]);
if(!empty($posnum)){
    $data=Data::QueryData("cms_pay_log","","posnum='$posnum'","","")["querydata"][0];
    $uid=$data["uid"];
    //社区整合数据
    $state=Data::QueryData("forum_payment","state","posnum='$posnum'","","")["querydata"][0]["state"];
    $amount=$data["amount"]*$set[0]["rate"];
    $money=Data::QueryData("forum_member","money","id='$uid'","","")["querydata"][0]["money"];
    $newmoney=round($money,2)+round($amount,2);
}
if(!empty($_GET["posnum"])){
    if($data["state"]==1 && $state==0){
        Data::UpdateData("forum_member",array("money"=>$newmoney),"id='$uid'");
        Data::UpdateData("forum_payment",array("state"=>1),"posnum='$posnum'");
        Inc::GoUrl(Route::Link("forum","my"),"充值成功!");
    }else{
        Inc::GoUrl("-1","充值失败!");
    }
}
if(!empty($_POST["posnum"])){
    if($data["state"]==1 && $state==0){
        Data::UpdateData("forum_member",array("money"=>$newmoney),"id='$uid'");
        Data::UpdateData("forum_payment",array("state"=>1),"posnum='$posnum'");
    }
}