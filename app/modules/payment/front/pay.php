<?php
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
use \PayPal\Api\Payer;
use \PayPal\Api\Item;
use \PayPal\Api\ItemList;
use \PayPal\Api\Details;
use \PayPal\Api\Amount;
use \PayPal\Api\Transaction;
use \PayPal\Api\RedirectUrls;
use \PayPal\Api\Payment;
use \PayPal\Exception\PayPalConnectionException;
$lb=UTInc::SqlCheck($_GET["lb"]);
$posnum=UTInc::SqlCheck($_GET["posnum"]);
if($lb=="alipay"):
    require_once MODULE_PATH.'/payment/alipay/Config.php';
    $amount=UTData::QueryData("cms_pay_log","amount","posnum='$posnum'","","")["querydata"][0]["amount"];
    //支付宝移动支付
    if(UTInc::IsApp()):
        require_once MODULE_PATH.'/payment/alipay/service/AlipayTradeWapPayService.php';
        require_once MODULE_PATH.'/payment/alipay/wappaybuilder/AlipayTradeWapPayContentBuilder.php';
        $timeout="1m";
        $payRequestBuilder = new AlipayTradeWapPayContentBuilder();
        $payRequestBuilder->setBody("订单号：".$posnum."，该订单从移动端支付。");
        $payRequestBuilder->setSubject("支付");
        $payRequestBuilder->setOutTradeNo($posnum);
        $payRequestBuilder->setTotalAmount($amount);
        $payRequestBuilder->setTimeExpress($timeout);
        $payResponse = new AlipayTradeService($alipay);
        $result=$payResponse->wapPay($payRequestBuilder,$alipay['return_url'],$alipay['notify_url']);
        return;
		//支付宝电脑支付
    else:
        require_once MODULE_PATH.'/payment/alipay/service/AlipayTradePagePayService.php';
        require_once MODULE_PATH.'/payment/alipay/pagepaybuilder/AlipayTradePagePayContentBuilder.php';
	    $payRequestBuilder = new AlipayTradePagePayContentBuilder();
	    $payRequestBuilder->setBody("订单号：".$posnum."，该订单从电脑端支付。");
	    $payRequestBuilder->setSubject("支付");
	    $payRequestBuilder->setTotalAmount($amount);
	    $payRequestBuilder->setOutTradeNo($posnum);
	    $aop = new AlipayTradeService($alipay);
	    $response = $aop->pagePay($payRequestBuilder,$alipay['return_url'],$alipay['notify_url']);


	    //var_dump($response);
    endif;
elseif($lb=="wechat"):
    require_once MODULE_PATH.'/payment/wechat/lib/WxPay.Api.php';
    require_once MODULE_PATH.'/payment/wechat/Log.php';
		$pay=UTData::QueryData("cms_pay_log","","posnum='$posnum'","","0")["querydata"][0];
		$amount=$pay["amount"]*100;
		//微信移动支付
    if(UTInc::IsApp()):
        require_once MODULE_PATH.'/payment/wechat/WxPay.JsApiPay.php';
				$logHandler= new CLogFileHandler(MODULE_PATH.'/payment/logs/wechat_'.date("Y-m-d").'.log');
				$log = Log::Init($logHandler, 15);
				function printf_info($data){
						foreach($data as $key=>$value){
								echo "<font color=red>".$key."</font> : ".$value." <br/>";
						}
				}
				$tools = new JsApiPay();
				$openId = $tools->GetOpenid();
				$input = new WxPayUnifiedOrder();
				$input->SetBody("支付".$pay["posnum"]."订单");
				$input->SetAttach("支付");
				$input->SetOut_trade_no($pay["posnum"]);
				$input->SetTotal_fee($amount);
				$input->SetTime_start(date("YmdHis"));
				$input->SetTime_expire(date("YmdHis", time() + 600));
				$input->SetGoods_tag("支付");
				$input->SetNotify_url(WxPayConfig::NOTIFY_URL);
				$input->SetTrade_type("JSAPI");
				$input->SetOpenid($openId);
				$order = WxPayApi::unifiedOrder($input);
				$jsApiParameters = $tools->GetJsApiParameters($order);
				$editAddress = $tools->GetEditAddressParameters();
				$return=UTData::QueryData("cms_pay_log","","posnum='$posnum'","","")["querydata"][0]["returnurl"];
				echo '<html>' . "\n";
				echo '<head>' . "\n";
				echo '    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>' . "\n";
				echo '    <meta name="viewport" content="width=device-width, initial-scale=1"/>' . "\n";
				echo '    <title>支付</title>' . "\n";
				echo '    <script type="text/javascript">' . "\n";
				echo '       function jsApiCall(){' . "\n";
				echo '           WeixinJSBridge.invoke(' . "\n";
				echo '              "getBrandWCPayRequest",' . "\n";
				echo '              ' . $jsApiParameters . ',' . "\n";
				echo '               function(res){' . "\n";
				echo '                   WeixinJSBridge.log(res.err_msg);' . "\n";
				echo '                   if(res.err_msg == "get_brand_wcpay_request:ok"){' . "\n";
				if(!empty($return)){
						echo '                       window.location.href = "' . str_replace("&amp;","&",$return) . '&posnum=' . $posnum . '";' . "\n";
				}else{
						echo '                       window.location.href = "' . $set["url_ok"] . '";' . "\n";
				}

				echo '                   }else{' . "\n";
				echo '                       window.location.href = "' . $set["url_no"] . '";' . "\n";
				echo '                   }' . "\n";
				echo '               }' . "\n";
				echo '           );' . "\n";
				echo '       }' . "\n";
				echo '    function callpay(){' . "\n";
				echo '        if (typeof WeixinJSBridge == "undefined"){' . "\n";
				echo '            if( document.addEventListener ){' . "\n";
				echo '                document.addEventListener("WeixinJSBridgeReady", jsApiCall, false);' . "\n";
				echo '            }else if (document.attachEvent){' . "\n";
				echo '                document.attachEvent("WeixinJSBridgeReady", jsApiCall);' . "\n";
				echo '                document.attachEvent("onWeixinJSBridgeReady", jsApiCall);' . "\n";
				echo '            }' . "\n";
				echo '        }else{' . "\n";
				echo '            jsApiCall();' . "\n";
				echo '        }' . "\n";
				echo '    }' . "\n";
				echo '    </script>' . "\n";
				echo '</head>' . "\n";
				echo '<body>' . "\n";
				echo '<div style="margin:0 auto;width:80%;font-size:18px;text-align:center;padding:10% 0;">' . "\n";
				echo '    <p>支付订单:' . $posnum . '</p>' . "\n";
				echo '    <button style="width:100%;height:50px;background-color:#FE6714; border:0px #FE6714 solid; color:white;font-size:16px;" type="button" onclick="callpay()">' . "\n";
				echo '        发起支付' . "\n";
				echo '    </button>' . "\n";
				echo '</div>' . "\n";
				echo '</body>' . "\n";
				echo '</html>';
		//微信电脑支付
    else:
				require_once MODULE_PATH.'/payment/wechat/WxPay.NativePay.php';
				$timediff=time()-strtotime($pay["postime"]);
				if($timediff>"7140"):
						UTInc::GoUrl("","支付时间失效!");
				else:
						$notify = new NativePay();
						$input = new WxPayUnifiedOrder();
						$input->SetBody("支付".$pay["posnum"]."订单");
						$input->SetAttach("支付");
						$input->SetOut_trade_no($pay["posnum"]);
						$input->SetTotal_fee($amount);
						$input->SetTime_start(date("YmdHis"));
						$input->SetTime_expire(date("YmdHis", time() + 600));
						$input->SetGoods_tag("payment1");
						$input->SetNotify_url(WxPayConfig::NOTIFY_URL);
						$input->SetTrade_type("NATIVE");
						$input->SetProduct_id("123456789");
						$result = $notify->GetPayUrl($input);
						$url2 = $result["code_url"];
						$return=UTData::QueryData("cms_pay_log","","posnum='$posnum'","","")["querydata"][0]["returnurl"];
						echo '<html>' . "\n";
						echo '<head>' . "\n";
						echo '    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>' . "\n";
						echo '    <meta name="viewport" content="width=device-width, initial-scale=1" />' . "\n";
						echo '    <title>支付</title>' . "\n";
						echo '    <script src="/assets/js/jquery.min.js"></script>' . "\n";
						echo '    <script>' . "\n";
						echo '    $(function(){setInterval(function(){check()}, 5000);})' . "\n";
						echo '        function check(){' . "\n";
						echo '            var url = "?m=payment&p=query&lb=wechat";' . "\n";
						echo '            var param = {\'out_trade_no\':\'' . $posnum . '\'};' . "\n";
						echo '            $.post(url, param, function(data){' . "\n";
						echo '                data = JSON.parse(data);' . "\n";
						echo '                if(data[\'trade_state\'] == "SUCCESS"){' . "\n";
						echo '                    alert("支付成功!");' . "\n";
						if(!empty($return)){
								echo '                    window.location.href = "' . str_replace("&amp;","&",$return) . '&posnum=' . $posnum . '";' . "\n";
						}else{
								echo '                    window.location.href = "' . $set["url_ok"] . '";' . "\n";
						}

						echo '                }else{' . "\n";
						echo '                    console.log(data);' . "\n";
						echo '                }' . "\n";
						echo '            });' . "\n";
						echo '        }' . "\n";
						echo '    </script>' . "\n";
						echo '</head>' . "\n";
						echo '<body>' . "\n";
						echo '<div style="margin:0 auto;width:80%;font-size:18px;text-align:center;padding:10% 0;">' . "\n";
						echo '    <p>支付订单:' . $posnum . '</p>' . "\n";
						echo '    <p><img alt="Qrcode Pay" src="?m=payment&p=qrcode&data=' . urlencode($url2) . '" style="width:200px;height:200px;"/></p>' . "\n";
						echo '</div>' . "\n";
						echo '</body>' . "\n";
						echo '</html>';
				endif;
		endif;
elseif($lb=="paypal"):
    require_once MODULE_PATH.'/payment/paypal/Config.php';
		$pay=library\UsualToolData\UTData::QueryData("cms_pay_log","","posnum='$posnum'","","1")["querydata"][0];
		$shipping = 0.00;
		$total = $pay["amount"] + $shipping;
		$payer = new Payer();
		$payer->setPaymentMethod('paypal');
		$item = new Item();
		$item->setName("Pay Order:".$posnum."")
				->setCurrency($pay["unit"])
				->setQuantity(1)
				->setPrice($pay["amount"]);
		$itemList = new ItemList();
		$itemList->setItems([$item]);
		$details = new Details();
		$details->setShipping($shipping)
				->setSubtotal($pay["amount"]);
		$amount = new Amount();
		$amount->setCurrency($pay["unit"])
				->setTotal($total)
				->setDetails($details);
		$transaction = new Transaction();
		$transaction->setAmount($amount)
				->setItemList($itemList)
				->setDescription("Pay ".$pay["posnum"]."")
				->setInvoiceNumber($pay["posnum"]);
		$redirectUrls = new RedirectUrls();
		$redirectUrls->setReturnUrl(SITE_URL.'/?m=payment&p=return&lb=paypal&success=true')->setCancelUrl(SITE_URL.'/?m=payment&p=return&lb=paypal&success=false');
		$payment = new Payment();
		$payment->setIntent('sale')
				->setPayer($payer)
				->setRedirectUrls($redirectUrls)
				->setTransactions([$transaction]);
		try {	
			$payment->create($paypal);
		} catch (PayPalConnectionException $e) {
				echo $e->getData();
				die();
		}
		$approvalUrl = $payment->getApprovalLink();
		header("Location: {$approvalUrl}");
endif;