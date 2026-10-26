<?php
require dirname(__FILE__).'/'.'power.php';
require dirname(__FILE__).'/'.'session.php';
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
if($utype!=99999):
    Inc::GoUrl("-1","权限不足!"); 
endif;
$pid=Inc::SqlCheck($_GET["pid"]);
$data=Data::QueryData("cms_plugin","","pid='$pid'");
$title=$data["querydata"][0]["title"];
$plugin_dir=APP_ROOT."/plugins/".$pid;
$plugin_file=$plugin_dir."/admin.php";
$config_file=$plugin_dir."/usualtool.config";
$code=is_file($config_file) ? Inc::StrSubstr("<code><![CDATA[","]]></code>",file_get_contents($config_file)) : "";
if(!empty($code) && $code!="0"){
    $code=preg_replace(array('/^\s*\?>/','/^\r?\n/'),'',$code);
    $code=str_replace("?m=ut-plugin&p=plugin_view",Route::Link("forum","my_admin_plugin_view"),$code);
    $sign=md5($code);
    $text=is_file($plugin_file) ? file_get_contents($plugin_file) : "";
    $marked=preg_match('/\/\*'.$pid.'-md5:([0-9a-f]{32})\*\//',$text,$m) ? $m[1] : "";
    if($text==="" || ($marked!=="" && $marked!==$sign)){
        @file_put_contents($plugin_file,"<?php /*".$pid."-md5:".$sign."*/?>\r\n".$code);
    }
}elseif(!is_file($plugin_file)){
    $plugin_file="";
}
$app->Runin(
    array("webplace","data","pid","plugin_file"),
    array($title,$data["querydata"],$pid,$plugin_file)
);
$app->Open("my_admin_plugin_view.cms");