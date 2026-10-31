<?php
namespace Controller\Payment\Front;
use usualtool\Lib\Inc;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\ExecutePayment;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;
use Model\Payment\Pay as PayModel;
class Notify{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $config;
        $model=new PayModel();
    $lb=Inc::SqlCheck($_GET["lb"] ?? "");
    /**
     * /?m=payment&p=notify&lb=alipay
     * /?m=payment&p=notify&lb=wechat
     * /?m=payment&p=notify&lb=paypal
    */
    if($lb=="alipay"):
    		require_once MODULE_PATH.'/payment/alipay/Config.php';
        require_once MODULE_PATH.'/payment/alipay/service/AlipayTradeService.php';
    		$arr=$_POST;
    		$alipaySevice = new \AlipayTradeService($alipay); 
    		$alipaySevice->writeLog(var_export($_POST,true));
    		$result = $alipaySevice->check($arr);
    		if($result):
    				$posnum = Inc::SqlCheck(trim($_POST['out_trade_no'] ?? ""));
    				$paynum = Inc::SqlCheck(trim($_POST['trade_no'] ?? ""));
    				$status = Inc::SqlCheck(trim($_POST['trade_status'] ?? ""));
    				$amount = Inc::SqlCheck(trim($_POST["total_amount"] ?? ""));
    				if($status == 'TRADE_FINISHED'):
    				    echo "success";
    				elseif($status == 'TRADE_SUCCESS'):
    						$model->MarkPaid($posnum,$paynum);
    						$return=$model->GetReturnUrl($posnum);
    						if(!empty($return)):
    						    Inc::HttpPost(str_replace("&amp;","&",$return),"posnum=".$posnum);
    						endif;
    						echo "success";
    				endif;
    		else:
    				echo "fail";
    		endif;
    elseif($lb=="wechat"):
    		ini_set('date.timezone', 'Asia/Shanghai');
    		require_once MODULE_PATH.'/payment/wechat/lib/WxPay.Api.php';
    		require_once MODULE_PATH.'/payment/wechat/lib/WxPay.Notify.php';
    		require_once MODULE_PATH.'/payment/wechat/lib/WxPay.Data.php';
    		$xml = file_get_contents("php://input");
    		$WxPayNotifyReply = new \WxPayNotifyReply();
    		$result = \WxPayResults::Init($xml);
    		$service_do_reslult = true;
    		$posnum = Inc::SqlCheck($result["out_trade_no"]);
    		$paynum = Inc::SqlCheck($result["transaction_id"]);
    		$amount = round(Inc::SqlCheck($result["total_fee"])/100,2); 
    		$model->MarkPaid($posnum,$paynum);
    		$return=$model->GetReturnUrl($posnum);
    		    if(!empty($return)):
    						Inc::HttpPost(str_replace("&amp;","&",$return),"posnum=".$posnum);
    				endif;
    		if($service_do_reslult):
    				$WxPayNotifyReply->SetReturn_code("SUCCESS");
    				$WxPayNotifyReply->SetReturn_msg("OK");
    				$WxPayNotifyReply->SetSign();  
    		else:
    				$WxPayNotifyReply->SetReturn_code("FAIL");
    				$WxPayNotifyReply->SetReturn_msg('UT ERROR!');  
    		endif;
    		\WxpayApi::replyNotify($WxPayNotifyReply->ToXml());
    		exit();
    elseif($lb=="paypal"):
    		require_once MODULE_PATH.'/payment/paypal/Config.php';
    		if(isset($_POST['success']) && $_POST['success'] == 'true'):
    				$paymentID = Inc::SqlCheck($_POST['paymentId'] ?? "");
    				$payerId = Inc::SqlCheck($_POST['PayerID'] ?? "");
    				$payment = Payment::get($paymentID, $paypal);
    				$execute = new PaymentExecution();
    				$execute->setPayerId($payerId);
    				$result = $payment->execute($execute, $paypal);
    				$paynum=$result->transactions[0]->related_resources[0]->sale->id;
    				$posnum=$result->transactions[0]->invoice_number;
    				$amount=$result->transactions[0]->amount->total;
    				$model->MarkPaid($posnum,$paynum);
    				$return=$model->GetReturnUrl($posnum);
    				if(!empty($return)):
    						Inc::HttpPost(str_replace("&amp;","&",$return),"posnum=".$posnum);
    				endif;
    				echo "success";
    				return $payment;
    		else:
    				echo "fail";
    		endif;
    endif;
    }
}