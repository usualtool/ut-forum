<?php
require dirname(__FILE__).'/'.'session.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
$data=UTData::QueryData("forum_member","","black=1")["querydata"];
$app->Runin(array("webplace","data"),array("小黑屋处罚公示",$data));
$app->Open("black.cms");