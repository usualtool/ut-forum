<?php
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
use library\UsualToolRoute\UTRoute;
$cookname=UTData::QueryData("forum_set","","","","1")["querydata"][0]["cookname"];
$data=UTData::QueryData("forum_member","","openid='".$_COOKIE["auth_openid"]."'","","");
if($data["querynum"]==1){
    setcookie("auth_openid",""); 
    setcookie("auth_name",""); 
    setcookie("auth_avatar","");
    $rows=$data["querydata"][0];
    if($rows["black"]==0){
        UTData::UpdateData("forum_member",array("lasttime"=>date('Y-m-d H:i:s',time())),"id='".$rows["id"]."'");
        UTData::InsertData("forum_member_log",array("uid"=>$rows["id"],"ip"=>UTInc::GetIp(),"content"=>"login","addtime"=>date('Y-m-d H:i:s',time())));
        $_SESSION[$cookname."uid"]=$rows["id"];
        $_SESSION[$cookname."username"]=$rows["username"];
        session_regenerate_id(TRUE);
        UTInc::GoUrl(UTRoute::Link("forum"),"第三方授权登陆成功!");
    }else{
        UTInc::GoUrl(UTRoute::Link("forum"),"账户已被封禁!");
    }
}else{
    $app->Runin("webplace","第三方账号授权");
    $app->Open("auth.cms");
}
if($_GET["do"]=="opensave"){
    $newid=UTData::InsertData("forum_member",array(
        "openid"=>UTInc::SqlCheck($_POST["openid"]),
        "avatar"=>UTInc::SqlCheck($_POST["avatar"]),
        "username"=>UTInc::SqlCheck($_POST["name"]),
        "password"=>"0",
        "salts"=>"0",
        "creattime"=>date('Y-m-d H:i:s',time())));
    if($newid){
        setcookie("auth_openid",""); 
        setcookie("auth_name",""); 
        setcookie("auth_avatar","");
        $rows=$data["querydata"][0];
        UTData::UpdateData("forum_member",array("lasttime"=>date('Y-m-d H:i:s',time())),"id='$newid'");
        UTData::InsertData("forum_member_log",array("uid"=>$newid,"ip"=>UTInc::GetIp(),"content"=>"login","addtime"=>date('Y-m-d H:i:s',time())));
        $_SESSION[$cookname."uid"]=$newid;
        $_SESSION[$cookname."username"]=UTInc::SqlCheck($_POST["name"]);
        session_regenerate_id(TRUE);
        UTInc::GoUrl(UTRoute::Link("forum"),"第三方授权登陆成功!");  
    }else{
        UTInc::GoUrl(UTRoute::Link("forum"),"一键登录失败!"); 
    }
}