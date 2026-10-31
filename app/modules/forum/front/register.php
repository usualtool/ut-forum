<?php
namespace Controller\Forum\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Forum\Member as MemberModel;
class Register{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $app,$config;
        $member=new MemberModel();
        $app->Runin(array("webplace"),array("创建账户"));
        $app->Open("register.cms");
        if(($_GET["do"] ?? "")=="register"){
            $username=Inc::SqlCheck($_POST["username"] ?? "");
            $password=Inc::SqlCheck($_POST["password"] ?? "");
            $passwords=Inc::SqlCheck($_POST["passwords"] ?? "");
            if(!empty($_POST["avatar"])){
                $avatar=Inc::SqlCheck($_POST["avatar"] ?? "");
            }else{
                $avatar=$config["APPURL"]."/assets/images/noimage.png";
            }
            $email=Inc::SqlCheck($_POST["email"] ?? "");
            $fullname=Inc::SqlCheck($_POST["fullname"] ?? "");
            $telephone=Inc::SqlCheck($_POST["telephone"] ?? "");
            $sex=Inc::SqlCheck($_POST["sex"] ?? "");
            if($password!=$passwords):
                Inc::GoUrl("-1","两次输入的密码不一致!");
            else:
                $passwordx=password_hash($password,PASSWORD_BCRYPT,array('cost'=>12));
                /**
                 * 单向同步UT会员模块
                 */
                $datanum=$member->CountNameOrEmail($username,$email);
                if($datanum>0):
                    Inc::GoUrl("-1","用户名或邮件已存在!");
                else:
                    if($member->Register($username,$passwordx,$avatar,$email,$fullname,$sex)):
                        Inc::GoUrl(Route::Link("forum","login"),"注册成功!");
                    else:
                        Inc::GoUrl("-1","注册失败!");
                    endif;
                endif;
            endif;
        }
    }
}