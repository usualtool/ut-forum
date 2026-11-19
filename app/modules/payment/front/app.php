<?php
namespace Controller\Payment\Front;
use usualtool\Lib\Inc;
use usualtool\Payment\Alipay\Sdk as AlipaySdk;
use usualtool\Payment\Wechat\Init as WechatInit;
use usualtool\Payment\Wechat\AppPay;
use usualtool\Payment\Douyin\DouyinPay;
use Model\Payment\Pay as PayModel;
class App{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        $model=new PayModel();
    $lb=$_GET["lb"] ?? "";
    /**
     * /?m=payment&p=app&lb=alipay
     * /?m=payment&p=app&lb=wechat
     * /?m=payment&p=app&lb=douyin
    */
    if($lb=="douyin"):
            $payset=$model->GetSet();
            $posnum=$_POST["posnum"] ?? ($_GET["posnum"] ?? "");
            $pay=$model->GetLog($posnum);
            $douyin=new DouyinPay($payset["dy_appid"] ?? "",$payset["dy_salt"] ?? "");
            if(!$douyin->Ready() || empty($pay["posnum"])):
                    echo json_encode(array("err_no"=>40001,"err_tips"=>"Douyin Pay ERROR!"));
            else:
                    global $config;
                    $notify=$config["APPURL"]."/?m=payment&p=notify&lb=douyin";
                    $result=$douyin->CreateOrder($pay["posnum"],$pay["amount"],"订单支付",mb_substr((string)$pay["remark"],0,100),$notify);
                    echo json_encode($result);
            endif;
    elseif($lb=="alipay"):
    		$payset=$model->GetSet();
    		AlipaySdk::Load($payset);
    		$buyerid=$_POST["buyerid"] ?? "";
    		$posnum=$_POST["posnum"] ?? "";
    		$pay=$model->GetLog($posnum);
    		$aop = new \AopClient();
    		$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
    		$aop->appId = $payset["ali_appid"] ?? "";
    		$aop->rsaPrivateKey = $payset["ali_private_key"] ?? "";
    		$aop->alipayrsaPublicKey = $payset["ali_public_key"] ?? "";
    		$aop->apiVersion = '1.0';
    		$aop->signType = 'RSA2';
    		$aop->postCharset='UTF-8';
    		$aop->format='json';
    		$request = new \AlipayTradeCreateRequest ();
    		$postdata=array("out_trade_no"=>$pay["posnum"],"total_amount"=>$pay["amount"],"subject"=>"实时订单结算","body"=>$pay["remark"],"buyer_id"=>$buyerid);
    		$request->setBizContent(json_encode($postdata));
    		$result = $aop->execute($request); 
    		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
    		$resultCode = $result->$responseNode->code;
    		if(!empty($resultCode)&&$resultCode == 10000):
    			echo json_encode($result->$responseNode);
    		else:
    			echo "0";
    		endif;
    elseif($lb=="wechat"):
    		WechatInit::Start($model->GetSet());
    		$appid=$_POST['appid'] ?? "";
    		$openid=$_POST['openid'] ?? "";
    		$fee=$_POST['fee'] ?? "";
    		$posnum = $_POST['posnum'] ?? "";
    		$apppay = new AppPay($appid,$openid,\WxPayConfig::MCHID,\WxPayConfig::KEY,$posnum,"Goods Payment",$fee,\WxPayConfig::NOTIFY_URL);
    		$return=$apppay->pay();
    		echo json_encode($return);
    endif;
    }
}