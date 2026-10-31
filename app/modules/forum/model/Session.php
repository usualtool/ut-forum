<?php
namespace Model\Forum;
use usualtool\Lib\Data;
class Session{
    public function Check(){
        $set=Data::QueryData("forum_set","","","","1")["querydata"];
        $cookname=$set[0]["cookname"];
        if(isset($_SESSION[$cookname."uid"]) && isset($_SESSION[$cookname."username"])):
            $uid=$_SESSION[$cookname."uid"] ?? "";
            $username=$_SESSION[$cookname."username"] ?? "";
            $user=Data::QueryData("forum_member","","id='$uid'","","")["querydata"];
            $utype=$user[0]["utype"];
            $money=$user[0]["money"];
            return array(
                "set"=>$set,
                "cookname"=>$cookname,
                "rate"=>$set[0]["rate"],
                "user"=>$user,
                "uid"=>$user[0]["id"],
                "utype"=>$utype,
                "username"=>$user[0]["username"],
                "money"=>$money,
                "avatar"=>$user[0]["avatar"]
            );
        endif;
        return array(
            "set"=>$set,
            "cookname"=>$cookname,
            "rate"=>$set[0]["rate"],
            "user"=>"",
            "uid"=>"0",
            "utype"=>"0",
            "username"=>"",
            "money"=>"0",
            "avatar"=>""
        );
    }
    public function SetLogin($cookname,$uid,$username){
        $_SESSION[$cookname."uid"]=$uid;
        $_SESSION[$cookname."username"]=$username;
    }
    public function ClearLogin($cookname){
        unset($_SESSION[$cookname."uid"]);
        unset($_SESSION[$cookname."username"]);
    }
    public function SetAuthcode($code){
        $_SESSION['authcode']=$code;
    }
}