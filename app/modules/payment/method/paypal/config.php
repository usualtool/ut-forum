<?php
require dirname(dirname(__FILE__)).'/'.'config.php';
require "lib/autoload.php";
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
$set=UTData::QueryData("cms_pay","","","","1")["querydata"][0];
define('SITE_URL',$config["APPURL"]);
$paypal = new \PayPal\Rest\ApiContext(
new \PayPal\Auth\OAuthTokenCredential($set["pp_clientid"],$set["pp_secret"])
);
$paypal->setConfig(array('mode'=>$set["pp_mod"]));
return $paypal;