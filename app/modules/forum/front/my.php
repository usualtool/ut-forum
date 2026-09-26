<?php
require dirname(__FILE__).'/'.'power.php';
require dirname(__FILE__).'/'.'session.php';
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
$loginlog=Data::QueryData("forum_member_log","","uid='$uid'","addtime desc","0,5")["querydata"];
$app->Runin(array("webplace","loginlog"),array("我的",$loginlog));
$app->Open("my.cms");
if($_GET["do"]=="payment"){
    $paytype=Inc::SqlCheck($_POST["paytype"]);
    $posnum=Inc::GetRandomString(14,"123456789");
    $amount=Inc::SqlCheck($_POST["amount"]);
    $remark="forum-|-".$uid."-|-Recharge/UT/1-|-".$amount;
    if(Data::InsertData("forum_payment",array(
        "state"=>0,
        "posnum"=>$posnum,
        "uid"=>$uid,
        "pid"=>0,
        "amount"=>$amount,
        "addtime"=>date('Y-m-d H:i:s',time())))){
         echo"<form style='display:none;' name='pay' method='post' action='".Route::Link("payment")."'>
              <input name='form' type='hidden' value='{$paytype}'>
              <input name='uid' type='hidden' value='{$uid}'>
              <input name='posnum' type='hidden' value='{$posnum}'>
              <input name='amount' type='hidden' value='{$amount}'>
              <input name='remark' type='hidden' value='{$remark}'>
              <input name='returnurl' type='hidden' value='".Route::Link("forum","pay")."'>
              </form>
              <script type='text/javascript'>
              function load_submit(){document.pay.submit()}load_submit();
              </script>";
    }else{
        Inc::GoUrl("-1","创建订单失败!请重试!");
    }
}