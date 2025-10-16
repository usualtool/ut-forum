<?php
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
use library\UsualToolRoute\UTRoute;
$app->Runin(array("webplace","salts"),array("创建账户",UTInc::GetRandomString(8)));
$app->Open("register.cms");
if($_GET["do"]=="register"){
    $username=UTInc::SqlCheck($_POST["username"]);
    $password=UTInc::SqlCheck($_POST["password"]);
    $passwords=UTInc::SqlCheck($_POST["passwords"]);
    $salts=UTInc::SqlCheck($_POST["salts"]);
    if(!empty($_POST["avatar"])){
        $avatar=UTInc::SqlCheck($_POST["avatar"]);
    }else{
        $avatar=$config["APPURL"]."/assets/images/noimage.png";
    }
    $email=UTInc::SqlCheck($_POST["email"]);
    $fullname=UTInc::SqlCheck($_POST["fullname"]);
    $telephone=UTInc::SqlCheck($_POST["telephone"]);
    $sex=UTInc::SqlCheck($_POST["sex"]);
    if($password!=$passwords):
        UTInc::GoUrl("-1","两次输入的密码不一致!");
    else:
        $passwordx=sha1($salts.$password);
        $datanum=UTData::QueryData("forum_member","","username='$username' or email='$email'","","")["querynum"];
        /**
         * 单向同步UT会员模块
         */
        if(UTData::ModTable("cms_member")):
            $ut_datanum=UTData::QueryData("cms_member","","username='$username' or email='$email'","","")["querynum"];
            $datanum=$datanum+$ut_datanum;
        endif;
        if($datanum>0):
            UTInc::GoUrl("-1","用户名或邮件已存在!");
        else:
            if(UTData::InsertData("forum_member",array(
                "username"=>$username,
                "password"=>$passwordx,
                "salts"=>$salts,
                "avatar"=>$avatar,
                "email"=>$email,
                "fullname"=>$fullname,
                "sex"=>$sex,
                "creattime"=>date('Y-m-d H:i:s',time())))):
                /**
                 * 单向同步UT会员模块
                 */
                if(UTData::ModTable("cms_member")):
                    UTData::InsertData("cms_member",array(
                        "username"=>$username,"password"=>$passwordx,
                        "salts"=>$salts,"balance"=>0,
                        "avatar"=>$avatar,"email"=>$email,
                        "fullname"=>$fullname,"sex"=>$sex,
                        "creattime"=>date('Y-m-d H:i:s',time())));
                endif;
                UTInc::GoUrl(UTRoute::Link("forum","login"),"注册成功!");    
            else:
                UTInc::GoUrl("-1","注册失败!"); 
            endif;
        endif;
    endif;
}