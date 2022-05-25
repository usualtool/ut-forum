<?php
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
$app->Runin(array("data"),array(UTData::QueryData("cms_pay","","","","1")["querydata"]));
$app->Open("example.cms");