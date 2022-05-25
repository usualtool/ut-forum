<?php
require 'config.php';
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\ExecutePayment;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
if(isset($_POST['success']) && $_POST['success'] == 'true'){
    $paymentID = UTInc::SqlCheck($_POST['paymentId']);
    $payerId = UTInc::SqlCheck($_POST['PayerID']);
    $payment = Payment::get($paymentID, $paypal);
    $execute = new PaymentExecution();
    $execute->setPayerId($payerId);
    try{
        $result = $payment->execute($execute, $paypal);
        $paynum=$result->transactions[0]->related_resources[0]->sale->id;
        $posnum=$result->transactions[0]->invoice_number;
        $amount=$result->transactions[0]->amount->total;
        //支付模块接口状态
        UTData::UpdateData("cms_pay_log",array(
            "state"=>1,
            "paynum"=>$paynum,
            "paytime"=>date('Y-m-d H:i:s',time())),"posnum='$posnum' and state=0");
        /**
         * 被动通知逻辑处理区域
         */ 
        $return=UTData::QueryData("cms_pay_log","","posnum='$posnum'","","")["querydata"][0]["returnurl"];
        if(!empty($return)){
            UTInc::HttpPost(str_replace("&amp;","&",$return),"posnum=".$posnum);
        }
        /** 
         * 被动通知逻辑处理区域
         */
        echo "success";
    }catch (Exception $ex) {
        echo "fail";
        exit(1);
    }
    return $payment;
} else {
    echo "fail";
}