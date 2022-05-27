<?php
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
use library\UsualToolRoute\UTRoute;
$goto=empty($_GET["goto"]) ? $_POST["goto"] : $_GET["goto"];
$gourl=empty($goto) ? UTRoute::Link("forum") : base64_decode(urldecode($goto));
$cookname=UTData::QueryData("forum_set","","","","1")["querydata"][0]["cookname"];
$app->Runin(array("webplace","goto","gourl"),array("登陆",$goto,$gourl));
if($_GET["do"]=="out"){
    unset($_SESSION[$cookname."uid"]);
    unset($_SESSION[$cookname."username"]);
    UTInc::GoUrl($gourl,"登出成功!");
}
if($_GET["do"]=="login"){
    $username=UTInc::SqlCheck($_POST["username"]);
    $password=UTInc::SqlCheck($_POST["password"]);
    $code=UTInc::SqlCheck(strtolower($_POST["code"]));
    if($_SESSION['authcode']==$code){
        if(!empty($username)&&!empty($password)){
            $data=UTData::QueryData("forum_member","","username='$username' and black=0","","");
            if($data["querynum"]==1){
                $rows=$data["querydata"][0];
                $shaupass=sha1($rows['salts'].$password);
                if($shaupass==$rows['password']){
                    UTData::UpdateData("forum_member",array("lasttime"=>date('Y-m-d H:i:s',time())),"id='".$rows["id"]."'");
                    $_SESSION[$cookname."uid"]=$rows["id"];
                    $_SESSION[$cookname."username"]=$rows["username"];
                    session_regenerate_id(TRUE);
                    UTInc::GoUrl($gourl,"登陆成功!");
                }else{
                    UTInc::GoUrl("-1","账户或密码不匹配!");
                }
            }else{
                UTInc::GoUrl("-1","账户不存在或已被封禁!");
            }
        }else{
            UTInc::GoUrl("-1","账户或密码不能为空!");
        }
    }else{
        UTInc::GoUrl("-1","验证码不正确!");
    }
}
$app->Open("login.cms");