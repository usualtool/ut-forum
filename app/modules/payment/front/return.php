<?php
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\ExecutePayment;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;
$lb=UTInc::SqlCheck($_GET["lb"]);
/**
 * /?m=payment&p=return&lb=alipay
 * /?m=payment&p=return&lb=wechat
 * /?m=payment&p=return&lb=paypal
*/
if($lb=="alipay"):
		require_once MODULE_PATH.'/payment/alipay/Config.php';
		require_once MODULE_PATH.'/payment/alipay/service/AlipayTradeService.php';
		$arr=$_GET;
		$alipaySevice = new AlipayTradeService($alipay); 
		$result = $alipaySevice->check($arr);
		if($result):
				$posnum = UTInc::SqlCheck(trim($_GET["out_trade_no"]));
				$paynum = UTInc::SqlCheck(trim($_GET["trade_no"]));
				$amount = UTInc::SqlCheck(trim($_GET["total_amount"]));
				UTData::UpdateData("cms_pay_log",array(
							"state"=>1,
							"paynum"=>$paynum,
							"paytime"=>date('Y-m-d H:i:s',time())),"posnum='$posnum' and state=0");
				$return=UTData::QueryData("cms_pay_log","","posnum='$posnum'","","")["querydata"][0]["returnurl"];
				if(!empty($return)):
						UTInc::GoUrl(str_replace("&amp;","&",$return)."&posnum=".$posnum,"支付成功");
				else:
						UTInc::GoUrl($set["url_ok"],"支付成功");
				endif;
		else:
				UTInc::GoUrl($set["url_no"],"支付失败");
		endif;
elseif($lb=="paypal"):
    require_once MODULE_PATH.'/payment/paypal/Config.php';
		if(isset($_GET['success']) && $_GET['success'] == 'true'):
				$paymentID = UTInc::SqlCheck($_GET['paymentId']);
				$payerId = UTInc::SqlCheck($_GET['PayerID']);
				$payment = Payment::get($paymentID, $paypal);
				$execute = new PaymentExecution();
				$execute->setPayerId($payerId);
				header("Content-type: text/html; charset=utf-8");
						$result = $payment->execute($execute, $paypal);
						$paynum=$result->transactions[0]->related_resources[0]->sale->id;
						$posnum=$result->transactions[0]->invoice_number;
						$amount=$result->transactions[0]->amount->total;
						UTData::UpdateData("cms_pay_log",array(
								"state"=>1,
								"paynum"=>$paynum,
								"paytime"=>date('Y-m-d H:i:s',time())),"posnum='$posnum' and state=0");
						$return=UTData::QueryData("cms_pay_log","","posnum='$posnum'","","")["querydata"][0]["returnurl"];
						if(!empty($return)){
								UTInc::GoUrl(str_replace("&amp;","&",$return)."&posnum=".$posnum,"支付成功");
						}else{
								UTInc::GoUrl($set["url_ok"],"支付成功");
						}
				return $payment;
		else:
				UTInc::GoUrl($set["url_no"],"支付失败");
		endif;
endif;