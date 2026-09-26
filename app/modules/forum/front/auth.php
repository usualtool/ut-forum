<?php
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
$cookname=Data::QueryData("forum_set","","","","1")["querydata"][0]["cookname"];
$data=Data::QueryData("forum_member","","openid='".$_COOKIE["auth_openid"]."'","","");
if($data["querynum"]==1){
    setcookie("auth_openid",""); 
    setcookie("auth_name",""); 
    setcookie("auth_avatar","");
    $rows=$data["querydata"][0];
    if($rows["black"]==0){
        Data::UpdateData("forum_member",array("lasttime"=>date('Y-m-d H:i:s',time())),"id='".$rows["id"]."'");
        Data::InsertData("forum_member_log",array("uid"=>$rows["id"],"ip"=>Inc::GetIp(),"content"=>"login","addtime"=>date('Y-m-d H:i:s',time())));
        $_SESSION[$cookname."uid"]=$rows["id"];
        $_SESSION[$cookname."username"]=$rows["username"];
        session_regenerate_id(TRUE);
        Inc::GoUrl(Route::Link("forum"),"第三方授权登陆成功!");
    }else{
        Inc::GoUrl(Route::Link("forum"),"账户已被封禁!");
    }
}else{
    $app->Runin("webplace","第三方账号授权");
    $app->Open("auth.cms");
}
if($_GET["do"]=="opensave"){
    $newid=Data::InsertData("forum_member",array(
        "openid"=>Inc::SqlCheck($_POST["openid"]),
        "avatar"=>Inc::SqlCheck($_POST["avatar"]),
        "username"=>Inc::SqlCheck($_POST["name"]),
        "password"=>"0",
        "salts"=>"0",
        "creattime"=>date('Y-m-d H:i:s',time())));
    if($newid){
        setcookie("auth_openid",""); 
        setcookie("auth_name",""); 
        setcookie("auth_avatar","");
        $rows=$data["querydata"][0];
        Data::UpdateData("forum_member",array("lasttime"=>date('Y-m-d H:i:s',time())),"id='$newid'");
        Data::InsertData("forum_member_log",array("uid"=>$newid,"ip"=>Inc::GetIp(),"content"=>"login","addtime"=>date('Y-m-d H:i:s',time())));
        $_SESSION[$cookname."uid"]=$newid;
        $_SESSION[$cookname."username"]=Inc::SqlCheck($_POST["name"]);
        session_regenerate_id(TRUE);
        Inc::GoUrl(Route::Link("forum"),"第三方授权登陆成功!");  
    }else{
        Inc::GoUrl(Route::Link("forum"),"一键登录失败!"); 
    }
}