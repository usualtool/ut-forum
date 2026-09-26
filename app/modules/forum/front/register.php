<?php
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
$app->Runin(array("webplace","salts"),array("创建账户",Inc::GetRandomString(8)));
$app->Open("register.cms");
if($_GET["do"]=="register"){
    $username=Inc::SqlCheck($_POST["username"]);
    $password=Inc::SqlCheck($_POST["password"]);
    $passwords=Inc::SqlCheck($_POST["passwords"]);
    $salts=Inc::SqlCheck($_POST["salts"]);
    if(!empty($_POST["avatar"])){
        $avatar=Inc::SqlCheck($_POST["avatar"]);
    }else{
        $avatar=$config["APPURL"]."/assets/images/noimage.png";
    }
    $email=Inc::SqlCheck($_POST["email"]);
    $fullname=Inc::SqlCheck($_POST["fullname"]);
    $telephone=Inc::SqlCheck($_POST["telephone"]);
    $sex=Inc::SqlCheck($_POST["sex"]);
    if($password!=$passwords):
        Inc::GoUrl("-1","两次输入的密码不一致!");
    else:
        $passwordx=password_hash($password,PASSWORD_BCRYPT,array('cost'=>12));
        $datanum=Data::QueryData("forum_member","","username='$username' or email='$email'","","")["querynum"];
        /**
         * 单向同步UT会员模块
         */
        if(Data::ModTable("cms_member")):
            $ut_datanum=Data::QueryData("cms_member","","username='$username' or email='$email'","","")["querynum"];
            $datanum=$datanum+$ut_datanum;
        endif;
        if($datanum>0):
            Inc::GoUrl("-1","用户名或邮件已存在!");
        else:
            if(Data::InsertData("forum_member",array(
                "username"=>$username,
                "password"=>$passwordx,
                "avatar"=>$avatar,
                "email"=>$email,
                "fullname"=>$fullname,
                "sex"=>$sex,
                "creattime"=>date('Y-m-d H:i:s',time())))):
                /**
                 * 单向同步UT会员模块
                 */
                if(Data::ModTable("cms_member")):
                    Data::InsertData("cms_member",array(
                        "username"=>$username,
                        "password"=>$passwordx,
                        "balance"=>0,
                        "avatar"=>$avatar,
                        "email"=>$email,
                        "fullname"=>$fullname,
                        "sex"=>$sex,
                        "creattime"=>date('Y-m-d H:i:s',time())));
                endif;
                Inc::GoUrl(Route::Link("forum","login"),"注册成功!");    
            else:
                Inc::GoUrl("-1","注册失败!"); 
            endif;
        endif;
    endif;
}