<?php
namespace Controller\Payment\Front;
use usualtool\Lib\Inc;
use Model\Payment\Pay as PayModel;
class Query{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        $model=new PayModel();
    $lb=Inc::SqlCheck($_GET["lb"] ?? "");
    /**
     * /?m=payment&p=query&lb=alipay
     * /?m=payment&p=return&lb=wechat
    */
    if($lb=="alipay"):
    		require_once MODULE_PATH.'/payment/alipay/Config.php';
    		require_once MODULE_PATH.'/payment/alipay/AopSdk.php';
    		$row=$model->GetRoutine("0");
    		$posnum=Inc::SqlCheck($_POST["posnum"] ?? "");
    		$aop = new \AopClient();
    		$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
    		$aop->appId = $row["alrteid"];
    		$aop->rsaPrivateKey = urldecode($row["alapppubkey"]);
    		$aop->alipayrsaPublicKey = urldecode($row["alpaypubkey"]);
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
        require_once MODULE_PATH.'/payment/wechat/lib/WxPay.Api.php';
        if(isset($_REQUEST["transaction_id"]) && $_REQUEST["transaction_id"] != ""):
            $transaction_id = Inc::SqlCheck($_REQUEST["transaction_id"]);
            $input = new \WxPayOrderQuery();
            $input->SetTransaction_id($transaction_id);
            echo json_encode(\WxPayApi::orderQuery($input));
            exit();
        endif;
        if(isset($_REQUEST["out_trade_no"]) && $_REQUEST["out_trade_no"] != ""):
            $out_trade_no = Inc::SqlCheck($_REQUEST["out_trade_no"]);
            $input = new \WxPayOrderQuery();
            $input->SetOut_trade_no($out_trade_no);
            echo json_encode(\WxPayApi::orderQuery($input));
            exit();
        endif;
    endif;
    }
}