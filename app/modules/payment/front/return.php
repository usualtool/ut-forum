<?php
namespace Controller\Payment\Front;
use usualtool\Lib\Inc;
use usualtool\Payment\Alipay\Sdk as AlipaySdk;
use usualtool\Payment\Paypal\Sdk as PaypalSdk;
use usualtool\Payment\Paypal\Factory as PaypalPay;
use Model\Payment\Pay as PayModel;
class Pay_return{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $config;
        $model=new PayModel();
    $lb=$_GET["lb"] ?? "";
    /**
     * /?m=payment&p=return&lb=alipay
     * /?m=payment&p=return&lb=wechat
     * /?m=payment&p=return&lb=paypal
    */
    if($lb=="alipay"):
    		$alipay=AlipaySdk::Load($model->GetSet());
    		\usualtool\Payment\Load::Sdk("Alipay/sdk/service/AlipayTradeWapPayService.php");
    		$arr=$_GET;
    		$alipaySevice = new \AlipayTradeService($alipay); 
    		$result = $alipaySevice->check($arr);
    		if($result):
    				$posnum = trim($_GET["out_trade_no"] ?? "");
    				$paynum = trim($_GET["trade_no"] ?? "");
    				$amount = trim($_GET["total_amount"] ?? "");
    				$model->MarkPaid($posnum,$paynum);
    				$return=$model->GetReturnUrl($posnum);
    				if(!empty($return)):
    						Inc::GoUrl(str_replace("&amp;","&",$return)."&posnum=".$posnum,"支付成功");
    				else:
    						Inc::GoUrl($set["url_ok"],"支付成功");
    				endif;
    		else:
    				Inc::GoUrl($set["url_no"],"支付失败");
    		endif;
    elseif($lb=="paypal"):
        $paypal=PaypalSdk::Start($model->GetSet(),$config["APPURL"]);
    		if(isset($_GET['success']) && $_GET['success'] == 'true'):
    				$result=PaypalPay::Execute($paypal,$_GET['paymentId'] ?? "",$_GET['PayerID'] ?? "");
    				if($result===false):
    						Inc::GoUrl($set["url_no"],"支付失败");
    						return false;
    				endif;
    				$paynum=$result["paynum"];
    				$posnum=$result["posnum"];
    				$amount=$result["amount"];
    				$model->MarkPaid($posnum,$paynum);
    				$return=$model->GetReturnUrl($posnum);
    				if(!empty($return)){
    						Inc::GoUrl(str_replace("&amp;","&",$return)."&posnum=".$posnum,"支付成功");
    				}else{
    						Inc::GoUrl($set["url_ok"],"支付成功");
    				}
    				return true;
    		else:
    				Inc::GoUrl($set["url_no"],"支付失败");
    		endif;
    endif;
    }
}