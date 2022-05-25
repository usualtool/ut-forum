<?php
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
$set=UTData::QueryData("forum_set","","","","1")["querydata"];
$cookname=$set[0]["cookname"];
if(isset($_SESSION[$cookname."uid"]) && isset($_SESSION[$cookname."username"])):
    $uid=$_SESSION[$cookname."uid"];
    $username=$_SESSION[$cookname."username"];
    $user=UTData::QueryData("forum_member","","id='$uid'","","")["querydata"];
    $utype=$user[0]["utype"];
    $money=$user[0]["money"];
    $app->Runin(array("rate","user","uid","utype","username","money","avatar"),array($set[0]["rate"],$user,$user[0]["id"],$utype,$user[0]["username"],$money,$user[0]["avatar"]));
else:
    $app->Runin(array("rate","user","uid","utype","username","money","avatar"),array($set[0]["rate"],"","0","0","","0",""));
endif;