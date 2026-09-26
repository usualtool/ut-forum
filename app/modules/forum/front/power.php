<?php
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
$cookname=Data::QueryData("forum_set","","","","1")["querydata"][0]["cookname"];
if(!isset($_SESSION[$cookname."uid"]) || !isset($_SESSION[$cookname."username"])):
    Inc::GoUrl(Route::Link("forum","login","goto=".urlencode(base64_encode(UTInc::CurPageUrl()))),"请登陆!");
endif;