<?php
header("Content-type: text/html; charset=utf-8");
require_once dirname(dirname(__FILE__)).'/config.php';
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'service/AlipayTradeService.php';
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'buildermodel/AlipayTradeWapPayContentBuilder.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
$posnum=UTInc::SqlCheck(trim($_GET["posnum"]));
$amount=UTData::QueryData("cms_pay_log","amount","posnum='$posnum'","","")["querydata"][0]["amount"];
    $timeout="1m";
    $payRequestBuilder = new AlipayTradeWapPayContentBuilder();
    $payRequestBuilder->setBody("订单号：".$posnum."，该订单从移动端支付。");
    $payRequestBuilder->setSubject("支付");
    $payRequestBuilder->setOutTradeNo($posnum);
    $payRequestBuilder->setTotalAmount($amount);
    $payRequestBuilder->setTimeExpress($timeout);
    $payResponse = new AlipayTradeService($alipay);
    $result=$payResponse->wapPay($payRequestBuilder,$alipay['return_url'],$alipay['notify_url']);
    return ;
?>