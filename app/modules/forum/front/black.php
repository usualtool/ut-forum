<?php
require dirname(__FILE__).'/'.'session.php';
use usualtool\Lib\Data;
$data=Data::QueryData("forum_member","","black=1")["querydata"];
$app->Runin(array("webplace","data"),array("小黑屋处罚公示",$data));
$app->Open("black.cms");