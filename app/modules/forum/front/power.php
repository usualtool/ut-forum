<?php
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
use library\UsualToolRoute\UTRoute;
$cookname=UTData::QueryData("forum_set","","","","1")["querydata"][0]["cookname"];
if(!isset($_SESSION[$cookname."uid"]) || !isset($_SESSION[$cookname."username"])):
    UTInc::GoUrl(UTRoute::Link("forum","login","goto=".urlencode(base64_encode(UTInc::CurPageUrl()))),"请登陆!");
endif;