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
$plugin_code=Inc::StrSubstr("<plugincode><![CDATA[","]]></plugincode>",$plugin);
$plugin_code=str_replace("?m=ut-plugin&p=plugin_view","?m=forum&p=my_admin_plugin_view",$plugin_code);
$app->Runin(array("webplace","data","plugin_code"),array($title,$data["querydata"],$plugin_code));
$app->Open("my_admin_plugin_view.cms");