<?php
namespace Controller\Payment\Front;
use usualtool\Lib\Inc;
use usualtool\Payment\Alipay\Sdk as AlipaySdk;
use usualtool\Payment\Wechat\Init as WechatInit;
use usualtool\Payment\Load;
use usualtool\Payment\Douyin\DouyinPay;
use Model\Payment\Pay as PayModel;
class Query{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        $model=new PayModel();
    $lb=$_GET["lb"] ?? "";
    /**
     * /?m=payment&p=query&lb=alipay
     * /?m=payment&p=return&lb=wechat
     * /?m=payment&p=query&lb=douyin
    */
    if($lb=="douyin"):
            $payset=$model->GetSet();
            $douyin=new DouyinPay($payset["dy_appid"] ?? "",$payset["dy_salt"] ?? "");
            $posnum=$_POST["posnum"] ?? ($_GET["posnum"] ?? "");
            if(!$douyin->Ready() || $posnum==""):
                    echo json_encode(array("result"=>"fail"));
            else:
                    $res=$douyin->QueryOrder($posnum);
                    if(($res["err_no"] ?? -1)==0 && ($res["data"]["status"] ?? "")=="SUCCESS"):
                            $model->MarkPaid($posnum,$res["data"]["order_id"] ?? "");
                            echo json_encode(array("result"=>"payok"));
                    else:
                            echo json_encode(array("result"=>"fail"));
                    endif;
            endif;
    elseif($lb=="alipay"):
    		$payset=$model->GetSet();
    		AlipaySdk::Load($payset);
    		$posnum=$_POST["posnum"] ?? "";
    		$aop = new \AopClient();
    		$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
    		$aop->appId = $payset["ali_appid"] ?? "";
    		$aop->rsaPrivateKey = urldecode($payset["ali_private_key"]);
    		$aop->alipayrsaPublicKey = urldecode($payset["ali_public_key"]);
    		$aop->apiVersion = '1.0';
    		$aop->signType = 'RSA2';
    		$aop->postCharset='UTF-8';
    		$aop->format='json';
    		$request = new \AlipayTradeQueryRequest();
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
    						$model->MarkPaidByPostnum($postnum,$trade_no);
    						echo json_encode(array("result"=>"payok"));
    				endif;
    		else:
    				echo json_encode(array("result"=>"fail"));
    		endif;
    elseif($lb=="wechat"):
        WechatInit::Start($model->GetSet());
        Load::Sdk("Wechat/lib/WxPay.Api.php");
        if(isset($_REQUEST["transaction_id"]) && $_REQUEST["transaction_id"] != ""):
            $transaction_id = $_REQUEST["transaction_id"];
            $input = new \WxPayOrderQuery();
            $input->SetTransaction_id($transaction_id);
            echo json_encode(\WxPayApi::orderQuery($input));
            exit();
        endif;
        if(isset($_REQUEST["out_trade_no"]) && $_REQUEST["out_trade_no"] != ""):
            $out_trade_no = $_REQUEST["out_trade_no"];
            $input = new \WxPayOrderQuery();
            $input->SetOut_trade_no($out_trade_no);
            echo json_encode(\WxPayApi::orderQuery($input));
            exit();
        endif;
    endif;
    }
}