<?php
header('content-type:application/json;charset=utf8');
require_once(dirname(dirname(__FILE__)).'/'.'config.php');
require_once(dirname(dirname(__FILE__)).'/'.'AopSdk.php');
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
$row=UTData::QueryData("cms_routine","","","","1")["querydata"][0];
//小程序传递参数
$buyerid=UTInc::SqlCheck($_POST["buyerid"]);
$posnum=UTInc::SqlCheck($_POST["posnum"]);
//查询支付数据
$pay=UTData::QueryData("cms_pay_log","","posnum='$posnum'","","")["querydata"][0];
$aop = new AopClient();
$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
$aop->appId = $row["alrteid"];
$aop->rsaPrivateKey = $row["alapppubkey"];
$aop->alipayrsaPublicKey = $row["alpaypubkey"];
$aop->apiVersion = '1.0';
$aop->signType = 'RSA2';
$aop->postCharset='UTF-8';
$aop->format='json';
$request = new AlipayTradeCreateRequest ();
$postdata=array("out_trade_no"=>$pay["posnum"],"total_amount"=>$pay["amount"],"subject"=>"实时订单结算","body"=>$pay["remark"],"buyer_id"=>$buyerid);
$request->setBizContent(json_encode($postdata));
$result = $aop->execute($request); 
$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
$resultCode = $result->$responseNode->code;
if(!empty($resultCode)&&$resultCode == 10000){
    echo json_encode($result->$responseNode);
}else{
    echo "0";
}