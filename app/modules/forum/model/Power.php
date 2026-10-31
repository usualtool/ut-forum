<?php
namespace Model\Forum;
use usualtool\Lib\Data;
class Power{
    public function Check(){
        $cookname=Data::QueryData("forum_set","","","","1")["querydata"][0]["cookname"];
        $login=isset($_SESSION[$cookname."uid"]) && isset($_SESSION[$cookname."username"]);
        return array("cookname"=>$cookname,"login"=>$login);
    }
}