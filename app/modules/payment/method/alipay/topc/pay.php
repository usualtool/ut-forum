<?php
require_once dirname(dirname(__FILE__)).'/config.php';
require_once dirname(__FILE__).'/service/AlipayTradeService.php';
require_once dirname(__FILE__).'/buildermodel/AlipayTradePagePayContentBuilder.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>支付</title>
</head>
<?php
$posnum=UTInc::SqlCheck(trim($_GET["posnum"]));
$amount=UTData::QueryData("cms_pay_log","amount","posnum='$posnum'","","")["querydata"][0]["amount"];
	$payRequestBuilder = new AlipayTradePagePayContentBuilder();
	$payRequestBuilder->setBody("订单号：".$posnum."，该订单从电脑端支付。");
	$payRequestBuilder->setSubject("支付");
	$payRequestBuilder->setTotalAmount($amount);
	$payRequestBuilder->setOutTradeNo($posnum);
	$aop = new AlipayTradeService($alipay);
	$response = $aop->pagePay($payRequestBuilder,$alipay['return_url'],$alipay['notify_url']);
	var_dump($response);
?>
</body>
</html>