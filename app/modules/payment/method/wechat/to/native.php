<?php
ini_set('date.timezone','Asia/Shanghai');
require_once dirname(dirname(__FILE__)).'/lib/WxPay.Api.php';
require_once dirname(__FILE__).'/WxPay.NativePay.php';
require_once dirname(__FILE__).'/log.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
$posnum=isset($_SESSION['posnum']) ? UTInc::SqlCheck($_SESSION['posnum']) : UTInc::SqlCheck($_GET["posnum"]);
$pay=UTData::QueryData("cms_pay_log","","posnum='$posnum'","","0")["querydata"][0];
$amount=$pay["amount"]*100;
$timediff=time()-strtotime($postime);
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
    ?>
    <html>
    <head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1" /> 
    <title><?php echo LangData('payment');?></title>
    <script src="<?php echo$config["APPURL"];?>/assets/js/jquery.min.js"></script>
    <script>
    $(function(){setInterval(function(){check()}, 5000);})
        function check(){
            var url = "../checkorder.php";
            var param = {'out_trade_no':'<?php echo$posnum;?>'};
            $.post(url, param, function(data){
                data = JSON.parse(data);
                if(data['trade_state'] == "SUCCESS"){
                    alert("支付成功!");
                    <?php if(!empty($return)){?>
                        window.location.href = "<?php echo str_replace("&amp;","&",$return);?>&posnum=<?php echo$posnum;?>";
                    <?php }else{?>
                        window.location.href = "<?php echo$set["url_ok"];?>";
                    <?php }?>
                }else{
                    console.log(data);
                }
            });
        }
    </script>
    </head>
    <body>
    <div style="margin:0 auto;width:80%;font-size:18px;text-align:center;padding:10% 0;">
        <p><?php echo LangData('ordernumber');?>:<?php echo$posnum;?></p>
        <p><img alt="Qrcode Pay" src="qrcode.php?data=<?php echo urlencode($url2);?>" style="width:200px;height:200px;"/></p>
    </div>
    </body>
    </html>
<?php endif;?>