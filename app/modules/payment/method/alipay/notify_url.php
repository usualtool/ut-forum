<?php
require_once 'config.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
if(UTInc::IsApp()){
    require_once 'tomb/service/AlipayTradeService.php';
}else{
    require_once 'topc/service/AlipayTradeService.php';
}
$arr=$_POST;
$alipaySevice = new AlipayTradeService($alipay); 
$alipaySevice->writeLog(var_export($_POST,true));
$result = $alipaySevice->check($arr);
if($result){
	$posnum = UTInc::SqlCheck(trim($_POST['out_trade_no']));
	$paynum = UTInc::SqlCheck(trim($_POST['trade_no']));
	$status = UTInc::SqlCheck(trim($_POST['trade_status']));
	$amount = UTInc::SqlCheck(trim($_POST["total_amount"]));
    if($status == 'TRADE_FINISHED'){
	    echo "success";
    }elseif($status == 'TRADE_SUCCESS'){
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
	    echo "success";
    }
}else {
    echo "fail";
}