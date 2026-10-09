<?php
require dirname(__FILE__).'/'.'power.php';
require dirname(__FILE__).'/'.'session.php';
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
if($utype!=99999):
    Inc::GoUrl("-1","权限不足!"); 
endif;
$pid=Inc::SqlCheck($_GET["pid"]);
$data=Data::QueryData("cms_plugin","","pid='$pid'");
$title=$data["querydata"][0]["title"];
$plugin=file_get_contents(APP_ROOT."/plugins/".$pid."/usualtool.config");
$code=Inc::StrSubstr("<code><![CDATA[","]]></code>",$plugin);
if(empty($code) || $code=="0"):
    $plugin_code="此插件无管理端<br/>This plugin has no management end";
else:
    $plugin_code=$code;
endif;
$plugin_code=str_replace("?m=ut-plugin&p=plugin_view","?m=forum&p=my_admin_plugin_view",$plugin_code);
$app->Runin(array("webplace","data","plugin_code"),array($title,$data["querydata"],$plugin_code));
$app->Open("my_admin_plugin_view.cms");