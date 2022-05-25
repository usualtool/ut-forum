<?php
require dirname(__FILE__).'/'.'power.php';
require dirname(__FILE__).'/'.'session.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
use library\UsualToolRoute\UTRoute;
$app->Runin(array("webplace"),array("我的"));
$app->Open("my.cms");
if($_GET["do"]=="payment"){
    $paytype=UTInc::SqlCheck($_POST["paytype"]);
    $posnum=UTInc::GetRandomString(14,"123456789");
    $amount=UTInc::SqlCheck($_POST["amount"]);
    $remark="forum-|-".$uid."-|-Recharge/UT/1-|-".$amount;
    if(UTData::InsertData("forum_payment",array(
        "state"=>0,
        "posnum"=>$posnum,
        "uid"=>$uid,
        "pid"=>0,
        "amount"=>$amount,
        "addtime"=>date('Y-m-d H:i:s',time())))){
         echo"<form style='display:none;' name='pay' method='post' action='".UTRoute::Link("payment")."'>
              <input name='form' type='hidden' value='{$paytype}'>
              <input name='uid' type='hidden' value='{$uid}'>
              <input name='posnum' type='hidden' value='{$posnum}'>
              <input name='amount' type='hidden' value='{$amount}'>
              <input name='remark' type='hidden' value='{$remark}'>
              <input name='returnurl' type='hidden' value='".UTRoute::Link("forum","pay")."'>
              </form>
              <script type='text/javascript'>
              function load_submit(){document.pay.submit()}load_submit();
              </script>";
    }else{
        UTInc::GoUrl("-1","创建订单失败!请重试!");
    }
}