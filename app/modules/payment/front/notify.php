<?php
namespace Controller\Payment\Front;
use usualtool\Lib\Inc;
use usualtool\Payment\Alipay\Sdk as AlipaySdk;
use usualtool\Payment\Wechat\Init as WechatInit;
use usualtool\Payment\Paypal\Sdk as PaypalSdk;
use usualtool\Payment\Paypal\Factory as PaypalPay;
use usualtool\Payment\Load;
use usualtool\Payment\Douyin\DouyinPay;
use Model\Payment\Pay as PayModel;
class Notify{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $config;
        $model=new PayModel();
    $lb=$_GET["lb"] ?? "";
    /**
     * /?m=payment&p=notify&lb=alipay
     * /?m=payment&p=notify&lb=wechat
     * /?m=payment&p=notify&lb=paypal
     * /?m=payment&p=notify&lb=douyin
    */
    if($lb=="douyin"):
            $payset=$model->GetSet();
            $douyin=new DouyinPay($payset["dy_appid"] ?? "",$payset["dy_salt"] ?? "");
            if($douyin->Ready() && $douyin->VerifyNotify($_POST)):
                    $msg=json_decode($_POST["msg"],true);
                    if(is_array($msg) && ($msg["status"] ?? "")=="SUCCESS"):
                            $posnum=trim($msg["cp_orderno"] ?? "");
                            $paynum=trim($msg["order_id"] ?? "");
                            $model->MarkPaid($posnum,$paynum);
                            $return=$model->GetReturnUrl($posnum);
                            if(!empty($return)):
                                    Inc::HttpPost(str_replace("&amp;","&",$return),"posnum=".$posnum);
                            endif;
                    endif;
                    echo json_encode(array("err_no"=>0,"err_tips"=>"success"));
            else:
                    echo json_encode(array("err_no"=>1,"err_tips"=>"verify fail"));
            endif;
    elseif($lb=="alipay"):
    		$alipay=AlipaySdk::Load($model->GetSet());
    		Load::Sdk("Alipay/sdk/service/AlipayTradeWapPayService.php");
    		$arr=$_POST;
    		$alipaySevice = new \AlipayTradeService($alipay); 
    		$alipaySevice->writeLog(var_export($_POST,true));
    		$result = $alipaySevice->check($arr);
    		if($result):
    				$posnum = trim($_POST['out_trade_no'] ?? "");
    				$paynum = trim($_POST['trade_no'] ?? "");
    				$status = trim($_POST['trade_status'] ?? "");
    				$amount = trim($_POST["total_amount"] ?? "");
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
    		WechatInit::Start($model->GetSet());
    		Load::Sdk("Wechat/lib/WxPay.Api.php");
    		Load::Sdk("Wechat/lib/WxPay.Notify.php");
    		Load::Sdk("Wechat/lib/WxPay.Data.php");
    		$xml = file_get_contents("php://input");
    		$WxPayNotifyReply = new \WxPayNotifyReply();
    		$result = \WxPayResults::Init($xml);
    		$service_do_reslult = true;
    		$posnum = $result["out_trade_no"];
    		$paynum = $result["transaction_id"];
    		$amount = round($result["total_fee"]/100,2); 
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
    		$paypal=PaypalSdk::Start($model->GetSet(),$config["APPURL"]);
    		if(isset($_POST['success']) && $_POST['success'] == 'true'):
    				$result=PaypalPay::Execute($paypal,$_POST['paymentId'] ?? "",$_POST['PayerID'] ?? "");
    				if($result===false):
    						echo "fail";
    						return false;
    				endif;
    				$paynum=$result["paynum"];
    				$posnum=$result["posnum"];
    				$amount=$result["amount"];
    				$model->MarkPaid($posnum,$paynum);
    				$return=$model->GetReturnUrl($posnum);
    				if(!empty($return)):
    						Inc::HttpPost(str_replace("&amp;","&",$return),"posnum=".$posnum);
    				endif;
    				echo "success";
    				return true;
    		else:
    				echo "fail";
    		endif;
    endif;
    }
}