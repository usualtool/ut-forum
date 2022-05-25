<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);
require_once dirname(__FILE__).'/lib/WxPay.Api.php';
require_once dirname(__FILE__).'/to/log.php';
use library\UsualToolInc\UTInc;
    //初始化日志
    $logHandler= new CLogFileHandler("./logs/".date('Y-m-d').'.log');
    $log = Log::Init($logHandler, 15);

    if(isset($_REQUEST["transaction_id"]) && $_REQUEST["transaction_id"] != ""){
        $transaction_id = UTInc::SqlCheck($_REQUEST["transaction_id"]);
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        echo json_encode(WxPayApi::orderQuery($input));
        exit();
    }
    if(isset($_REQUEST["out_trade_no"]) && $_REQUEST["out_trade_no"] != ""){
        $out_trade_no = UTInc::SqlCheck($_REQUEST["out_trade_no"]);
        $input = new WxPayOrderQuery();
        $input->SetOut_trade_no($out_trade_no);
        echo json_encode(WxPayApi::orderQuery($input));
        exit();
    }
?>