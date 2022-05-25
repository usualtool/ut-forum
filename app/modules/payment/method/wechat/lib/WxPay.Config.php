<?php
require dirname(dirname(dirname(__FILE__))).'/'.'config.php';
use library\UsualToolData\UTData;
$set=UTData::QueryData("cms_pay","","","","1")["querydata"][0];
define('wx_appid',$set["wx_appid"]);
define('wx_mchid',$set["wx_mchid"]);
define('wx_key',$set["wx_key"]);
define('wx_secert',$set["wx_secert"]);
define('wx_notify_url',$set["wx_notify_url"]);
class WxPayConfig{
	const APPID = wx_appid;
	const MCHID = wx_mchid;
	const KEY = wx_key;
	const APPSECRET = wx_secert;
	const NOTIFY_URL= wx_notify_url;
	const SSLCERT_PATH = '../cert/apiclient_cert.pem';
	const SSLKEY_PATH = '../cert/apiclient_key.pem';
	const CURL_PROXY_HOST = "0.0.0.0";
	const CURL_PROXY_PORT = 0;
	const REPORT_LEVENL = 1;
}