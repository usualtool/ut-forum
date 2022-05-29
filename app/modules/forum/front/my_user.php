<?php
require dirname(__FILE__).'/'.'power.php';
require dirname(__FILE__).'/'.'session.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
use library\UsualToolRoute\UTRoute;
$app->Runin(array("webplace"),array("更新账户"));
$app->Open("my_user.cms");
if($_GET["do"]=="save"){
    $id=UTInc::SqlCheck($_POST["id"]);
    $avatar=UTInc::SqlCheck($_POST["avatar"]);
    $fullname=UTInc::SqlCheck($_POST["fullname"]);
    $email=UTInc::SqlCheck($_POST["email"]);
    $telephone=UTInc::SqlCheck($_POST["telephone"]);
    $sex=UTInc::SqlCheck($_POST["sex"]);
    $password=UTInc::SqlCheck($_POST["password"]);
    $passwords=UTInc::SqlCheck($_POST["passwords"]);
    if(!empty($password) && !empty($passwords)):
        if($password!=$passwords):
            UTInc::GoUrl("-1","两次密码不一致");
        else:
            $salts=UTInc::GetRandomString(6);
            $passwordx=sha1($salts.$password);
            $sql=UTData::UpdateData("forum_member",array(
                "avatar"=>$avatar,
                "password"=>$passwordx,
                "salts"=>$salts,
                "fullname"=>$fullname,
                "telephone"=>$telephone,
                "email"=>$email,
                "sex"=>$sex),"id='$id'"); 
        endif;
    else:
        $sql=UTData::UpdateData("forum_member",array(
            "avatar"=>$avatar,
            "fullname"=>$fullname,
            "email"=>$email,
            "telephone"=>$telephone,
            "sex"=>$sex),"id='$id'");
    endif;
    if($sql):
        UTInc::GoUrl(UTRoute::Link("forum","my_user"),"更新资料成功!");
    else:
        UTInc::GoUrl("-1","更新资料失败!");
    endif;
}