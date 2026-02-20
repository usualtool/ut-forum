<?php
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
$lb=UTInc::SqlCheck($_GET["lb"]);
/**
 * /?m=payment&p=query&lb=alipay
 * /?m=payment&p=return&lb=wechat
*/
if($lb=="alipay"):
		require_once MODULE_PATH.'/payment/alipay/Config.php';
		require_once MODULE_PATH.'/payment/alipay/AopSdk.php';
		$row=UTData::QueryData("cms_routine","","","","1","0")["querydata"][0];
		$posnum=UTInc::SqlCheck($_POST["posnum"]);
		$aop = new AopClient();
		$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
		$aop->appId = $row["alrteid"];
		$aop->rsaPrivateKey = urldecode($row["alapppubkey"]);
		$aop->alipayrsaPublicKey = urldecode($row["alpaypubkey"]);
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
		if(!empty($resultCode)&&$resultCode == 10000):
				$res=$result->$responseNode;
				$trade_no=$res->trade_no;
				$trade_status=$res->trade_status;
				if($trade_status=="TRADE_FINISHED"):
						echo json_encode(array("result"=>"success"));
				elseif($trade_status=='TRADE_SUCCESS'):
						UTData::UpdateData("cms_pay_log",array("state"=>1,"paynum"=>$trade_no,"paytime"=>date('Y-m-d H:i:s',time())),"postnum='$postnum' and state=0");
						echo json_encode(array("result"=>"payok"));
				endif;
		else:
				echo json_encode(array("result"=>"fail"));
		endif;
elseif($lb=="wechat"):
    require_once MODULE_PATH.'/payment/wechat/lib/WxPay.Api.php';
    require_once MODULE_PATH.'/payment/wechat/Log.php';
    $logHandler= new CLogFileHandler("../logs/wechat_".date('Y-m-d').'.log');
    $log = Log::Init($logHandler, 15);
    if(isset($_REQUEST["transaction_id"]) && $_REQUEST["transaction_id"] != ""):
        $transaction_id = UTInc::SqlCheck($_REQUEST["transaction_id"]);
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        echo json_encode(WxPayApi::orderQuery($input));
        exit();
    endif;
    if(isset($_REQUEST["out_trade_no"]) && $_REQUEST["out_trade_no"] != ""):
        $out_trade_no = UTInc::SqlCheck($_REQUEST["out_trade_no"]);
        $input = new WxPayOrderQuery();
        $input->SetOut_trade_no($out_trade_no);
        echo json_encode(WxPayApi::orderQuery($input));
        exit();
    endif;
endif;