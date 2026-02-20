<?php
use library\UsualToolData\UTData;
$set=UTData::QueryData("cms_pay","","","","1")["querydata"][0];
$alipay=array (	
		'app_id' => $set["ali_appid"],
		'merchant_private_key' => urldecode($set["ali_private_key"]),
		'notify_url' => $set["ali_notify_url"],
		'return_url' => $set["ali_return_url"],
		'charset' => "UTF-8",
		'sign_type'=>"RSA2",
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",
		'alipay_public_key' => urldecode($set["ali_public_key"]),
);