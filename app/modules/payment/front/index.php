<?php
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
$payurl="/?m=payment&p=pay";
$uid=empty($_POST["uid"]) ? 0 : UTInc::SqlCheck($_POST["uid"]);
$form=empty($_POST["form"]) ? 1 : UTInc::SqlCheck($_POST["form"]);
$posnum=empty($_POST["posnum"]) ? UTInc::GetRandomString(14,"0123456789") : UTInc::SqlCheck($_POST["posnum"]);
$amount=empty($_POST["amount"]) ? 0 : UTInc::SqlCheck($_POST["amount"]);
$unit=empty($_POST["unit"]) ? "CNY" : UTInc::SqlCheck($_POST["unit"]);
$remark=empty($_POST["remark"]) ? "" : UTInc::SqlCheck($_POST["remark"]);
$returnurl=empty($_POST["returnurl"]) ? "" : UTInc::SqlCheck($_POST["returnurl"]);
$result=UTData::InsertData("cms_pay_log",array(
    "uid"=>$uid,
    "form"=>$form,
    "state"=>0,
    "posnum"=>$posnum,
    "amount"=>$amount,
    "unit"=>$unit,
    "remark"=>$remark,
    "returnurl"=>$returnurl,
    "postime"=>date('Y-m-d H:i:s',time())));  
if($result){
    if($form==1 || $form=="alipay"){
        UTInc::GoUrl($payurl."&lb=alipay&posnum=".$posnum,"");   
    }elseif($form==2 || $form=="wechat"){
        UTInc::GoUrl($payurl."&lb=wechat&posnum=".$posnum,"");   
    }elseif($form==3 || $form=="paypal"){
        UTInc::GoUrl($payurl."&lb=paypal&posnum=".$posnum,"");
    }
}else{
    UTInc::GoUrl("","支付错误,请重试!");
}