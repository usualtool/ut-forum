<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>支付结果</title>
</head>
<body>
<?php
require_once 'config.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
if(UTInc::IsApp()){
    require_once 'tomb/service/AlipayTradeService.php';
}else{
    require_once 'topc/service/AlipayTradeService.php';
}
$arr=$_GET;
$alipaySevice = new AlipayTradeService($alipay); 
$result = $alipaySevice->check($arr);
if($result){
	$posnum = UTInc::SqlCheck(trim($_GET["out_trade_no"]));
	$paynum = UTInc::SqlCheck(trim($_GET["trade_no"]));
	$amount = UTInc::SqlCheck(trim($_GET["total_amount"]));
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
        UTInc::GoUrl(str_replace("&amp;","&",$return)."&posnum=".$posnum,"支付成功");
    }else{
        UTInc::GoUrl($set["url_ok"],"支付成功");
    }
    /**
     * 被动通知逻辑处理区域
     */	
}else{
    UTInc::GoUrl($set["url_no"],"支付失败");
}
?>
</body>
</html>