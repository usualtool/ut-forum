<?php
ini_set('date.timezone','Asia/Shanghai');
require_once dirname(dirname(__FILE__)).'/lib/WxPay.Api.php';
require_once dirname(__FILE__).'/WxPay.JsApiPay.php';
require_once dirname(__FILE__).'/log.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);
function printf_info($data){
    foreach($data as $key=>$value){
        echo "<font color='#00ff55;'>$key</font> : $value <br/>";
    }
}
$tools = new JsApiPay();
$openId = $tools->GetOpenid();
if(isset($_SESSION['posnum'])):
    $posnum=UTInc::SqlCheck($_SESSION['posnum']);
elseif(!empty($_POST["posnum"])):
    $posnum=UTInc::SqlCheck($_POST["posnum"]);
else:
    $posnum=UTInc::SqlCheck($_GET["posnum"]);
endif;
$pay=UTData::QueryData("cms_pay_log","","posnum='$posnum'","","0")["querydata"][0];
$amount=$pay["amount"]*100;
//创建订单
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
?>
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/> 
    <title><?php echo LangData('payment');?></title>
    <script type="text/javascript">
       function jsApiCall(){
           WeixinJSBridge.invoke(
              'getBrandWCPayRequest',
              <?php echo $jsApiParameters; ?>,
               function(res){
                   WeixinJSBridge.log(res.err_msg);
                   if(res.err_msg == "get_brand_wcpay_request:ok"){
                       <?php if(!empty($return)){?>
                           window.location.href = "<?php echo str_replace("&amp;","&",$return);?>&posnum=<?php echo$posnum;?>";
                       <?php }else{?>
                           window.location.href="<?php echo$set["url_ok"];?>";
                       <?php }?>
                   }else{
                       window.location.href="<?php echo$set["url_no"];?>";
                         
                   }
               }
           );
       }
	function callpay(){
		if (typeof WeixinJSBridge == "undefined"){
		    if( document.addEventListener ){
		        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
		    }else if (document.attachEvent){
		        document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
		        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
		    }
		}else{
		    jsApiCall();
		}
	}
	</script>
</head>
<body>
<div style="margin:0 auto;width:80%;font-size:18px;text-align:center;padding:10% 0;">
    <p>支付订单:<?php echo$posnum;?></p>
    <button style="width:100%;height:50px;background-color:#FE6714; border:0px #FE6714 solid; color:white;font-size:16px;" type="button" onclick="callpay()" >
        发起支付
    </button>
</div>
</body>
</html>