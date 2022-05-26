<?php
require dirname(__FILE__).'/'.'power.php';
require dirname(__FILE__).'/'.'session.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
use library\UsualToolRoute\UTRoute;
$pid=UTInc::SqlCheck($_GET["pid"]);
$data=UTData::QueryData("cms_plugin","","pid='$pid'");
$title=$data["querydata"][0]["title"];
$plugin=file_get_contents(APP_ROOT."/plugins/".$pid."/usualtool.config");
$plugin_code=UTInc::StrSubstr("<plugincode><![CDATA[","]]></plugincode>",$plugin);
$app->Runin(array("webplace","data","plugin_code"),array($title,$data["querydata"],$plugin_code));
$app->Open("my_admin_plugin_view.cms");