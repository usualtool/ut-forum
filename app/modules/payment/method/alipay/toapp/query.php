<?php
header('content-type:application/json;charset=utf8');
require_once(dirname(dirname(__FILE__)).'/'.'config.php');
require_once(dirname(dirname(__FILE__)).'/'.'AopSdk.php');
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
$row=UTData::QueryData("cms_routine","","","","1","0")["querydata"][0];
$posnum=UTInc::SqlCheck($_POST["posnum"]);
$aop = new AopClient();
$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
$aop->appId = $row["alrteid"];
$aop->rsaPrivateKey = $row["alapppubkey"];
$aop->alipayrsaPublicKey = $row["alpaypubkey"];
$aop->apiVersion = '1.0';
$aop->signType = 'RSA2';
$aop->postCharset='UTF-8';
$aop->format='json';
$request = new AlipayTradeQueryRequest();
$postdata=array("out_trade_no"=>$ordernum);
$request->setBizContent(json_encode($postdata));
$result = $aop->execute($request); 
$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
$resultCode = $result->$responseNode->code;
if(!empty($resultCode)&&$resultCode == 10000){
    $res=$result->$responseNode;
    $trade_no=$res->trade_no;
    $trade_status=$res->trade_status;
    if($trade_status=="TRADE_FINISHED"){
        echo json_encode(array("result"=>"success"));
    }elseif($trade_status=='TRADE_SUCCESS'){
        UTData::UpdateData("cms_pay_log",array("state"=>1,"paynum"=>$trade_no,"paytime"=>date('Y-m-d H:i:s',time())),"postnum='$postnum' and state=0");
        echo json_encode(array("result"=>"payok"));
    }
}else{
    echo json_encode(array("result"=>"fail"));
}