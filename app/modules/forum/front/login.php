<?php
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
$goto=empty($_GET["goto"]) ? $_POST["goto"] : $_GET["goto"];
$gourl=empty($goto) ? Route::Link("forum") : base64_decode(urldecode($goto));
$cookname=Data::QueryData("forum_set","","","","1")["querydata"][0]["cookname"];
$app->Runin(array("webplace","goto","gourl"),array("登陆",$goto,$gourl));
if($_GET["do"]=="out"){
    unset($_SESSION[$cookname."uid"]);
    unset($_SESSION[$cookname."username"]);
    Inc::GoUrl($gourl,"登出成功!");
}
if($_GET["do"]=="login"){
    $username=Inc::SqlCheck($_POST["username"]);
    $password=Inc::SqlCheck($_POST["password"]);
    $code=Inc::SqlCheck(strtolower($_POST["code"]));
    if($_SESSION['authcode']==$code){
        if(!empty($username) && !empty($password)){
            $data=Data::QueryData("forum_member","","(username='$username' || email='$username' || telephone='$username') and black=0","","");
            if($data["querynum"]==1){
                $rows=$data["querydata"][0];
                if(password_verify($password,$rows["password"])){
                    Data::UpdateData("forum_member",array("lasttime"=>date('Y-m-d H:i:s',time())),"id='".$rows["id"]."'");
                    Data::InsertData("forum_member_log",array("uid"=>$rows["id"],"ip"=>Inc::GetIp(),"content"=>"login","addtime"=>date('Y-m-d H:i:s',time())));
                    $_SESSION[$cookname."uid"]=$rows["id"];
                    $_SESSION[$cookname."username"]=$rows["username"];
                    session_regenerate_id(TRUE);
                    Inc::GoUrl($gourl,"登陆成功!");
                }else{
                    Inc::GoUrl("-1","账户或密码不匹配!");
                }
            }else{
                Inc::GoUrl("-1","账户不存在或已被封禁!");
            }
        }else{
            Inc::GoUrl("-1","账户或密码不能为空!");
        }
    }else{
        Inc::GoUrl("-1","验证码不正确!");
    }
}
$app->Open("login.cms");