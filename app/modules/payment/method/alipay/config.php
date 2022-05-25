<?php
require dirname(dirname(__FILE__)).'/'.'config.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
$set=UTData::QueryData("cms_pay","","","","1")["querydata"][0];
$alipay=array (	
		//应用ID,您的APPID。
		'app_id' => $set["ali_appid"],
		//商户私钥
		'merchant_private_key' => $set["ali_private_key"],
		//异步通知地址
		'notify_url' => $set["ali_notify_url"],
		//同步跳转
		'return_url' => $set["ali_return_url"],
		//编码格式
		'charset' => "UTF-8",
		//签名方式
		'sign_type'=>"RSA2",
		//支付宝网关
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",
		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => $set["ali_public_key"],
);