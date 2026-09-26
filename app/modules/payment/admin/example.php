<?php
use usualtool\Lib\Data;
$app->Runin(array("data"),array(Data::QueryData("cms_pay","","","","1")["querydata"]));
$app->Open("example.cms");