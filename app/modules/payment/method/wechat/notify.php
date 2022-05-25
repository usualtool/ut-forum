<?php
ini_set('date.timezone', 'Asia/Shanghai');
error_reporting(E_ERROR);
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
require_once dirname(__FILE__).'/lib/WxPay.Api.php';
require_once dirname(__FILE__).'/lib/WxPay.Notify.php';
require_once dirname(__FILE__).'/lib/WxPay.Data.php';
$xml = file_get_contents("php://input");
$WxPayNotifyReply = new WxPayNotifyReply();
try {
    $result = WxPayResults::Init($xml);
} catch (WxPayException $e){
    $msg = $e->errorMessage();
    $WxPayNotifyReply->SetReturn_code("FAIL");
    $WxPayNotifyReply->SetReturn_msg($msg);
    WxpayApi::replyNotify($WxPayNotifyReply->ToXml());
    exit;
}
$service_do_reslult = true;
try {
    $posnum = UTInc::SqlCheck($result["out_trade_no"]);
    $paynum = UTInc::SqlCheck($result["transaction_id"]);
    $amount = round(UTInc::SqlCheck($result["total_fee"])/100,2); 
    //支付模块接口状态
        UTData::UpdateData("cms_pay_log",array(
            "state"=>1,
            "paynum"=>$paynum,
            "paytime"=>date('Y-m-d H:i:s',time())),"posnum='$posnum' and state=0");
        /**
         * 被动通知逻辑处理区域
         */
        $return=UTData::QueryData("cms_pay_log","","posnum='$posnum'","","")["querydata"][0]["returnurl"];
        if(!empty($return)){
            UTInc::HttpPost(str_replace("&amp;","&",$return),"posnum=".$posnum);
        }
        /**
         * 被动通知逻辑处理区域
         */
} catch (WxPayException $e) {
    $service_do_reslult = false;
    $msg = $e->errorMessage();
}
if($service_do_reslult){
    $WxPayNotifyReply->SetReturn_code("SUCCESS");
    $WxPayNotifyReply->SetReturn_msg("OK");
    $WxPayNotifyReply->SetSign();  
}else{
    $this->SetReturn_code("FAIL");
    $this->SetReturn_msg('UT ERROR!');  
}
WxpayApi::replyNotify($WxPayNotifyReply->ToXml());
exit();