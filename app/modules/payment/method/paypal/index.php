<?php
use \PayPal\Api\Payer;
use \PayPal\Api\Item;
use \PayPal\Api\ItemList;
use \PayPal\Api\Details;
use \PayPal\Api\Amount;
use \PayPal\Api\Transaction;
use \PayPal\Api\RedirectUrls;
use \PayPal\Api\Payment;
use \PayPal\Exception\PayPalConnectionException;
require "config.php";
$posnum=library\UsualToolInc\UTInc::SqlCheck($_GET["posnum"]);
$pay=library\UsualToolData\UTData::QueryData("cms_pay_log","","posnum='$posnum'","","1")["querydata"][0];
$shipping = 0.00;//运费
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
$redirectUrls->setReturnUrl(SITE_URL.'/app/modules/payment/method/paypal/return.php?success=true')
             ->setCancelUrl(SITE_URL.'/app/modules/payment/method/paypal/return.php?success=false');
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
?>