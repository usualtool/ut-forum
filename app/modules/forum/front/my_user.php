<?php
namespace Controller\Forum\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Forum\Member as MemberModel;
use Model\Forum\Session as SessionModel;
use Model\Forum\Power as PowerModel;
class My_user{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $app;
        $member=new MemberModel();
        $power=(new PowerModel())->Check();
        if(empty($power["login"])):
            Inc::GoUrl(Route::Link("forum","login","goto=".urlencode(base64_encode(Inc::CurPageUrl()))),"请登陆!");
        endif;
        $session=(new SessionModel())->Check();
        $app->Runin(
            array("rate","user","uid","utype","username","money","avatar"),
            array($session["rate"],$session["user"],$session["uid"],$session["utype"],$session["username"],$session["money"],$session["avatar"])
        );
        $app->Runin(array("webplace"),array("更新账户"));
        $app->Open("my_user.cms");
        if(($_GET["do"] ?? "")=="save"){
            $id=Inc::SqlCheck($_POST["id"] ?? "");
            $avatar=Inc::SqlCheck($_POST["avatar"] ?? "");
            $fullname=Inc::SqlCheck($_POST["fullname"] ?? "");
            $email=Inc::SqlCheck($_POST["email"] ?? "");
            $telephone=Inc::SqlCheck($_POST["telephone"] ?? "");
            $sex=Inc::SqlCheck($_POST["sex"] ?? "");
            $password=Inc::SqlCheck($_POST["password"] ?? "");
            $passwords=Inc::SqlCheck($_POST["passwords"] ?? "");
            if(!empty($password) && !empty($passwords)):
                if($password!=$passwords):
                    Inc::GoUrl("-1","两次密码不一致");
                else:
                    $passwordx=password_hash($password,PASSWORD_BCRYPT,array('cost'=>12));
                    $sql=$member->Update(array(
                        "avatar"=>$avatar,
                        "password"=>$passwordx,
                        "fullname"=>$fullname,
                        "telephone"=>$telephone,
                        "email"=>$email,
                        "sex"=>$sex),"id='$id'");
                endif;
            else:
                $sql=$member->Update(array(
                    "avatar"=>$avatar,
                    "fullname"=>$fullname,
                    "email"=>$email,
                    "telephone"=>$telephone,
                    "sex"=>$sex),"id='$id'");
            endif;
            if($sql):
                Inc::GoUrl(Route::Link("forum","my_user"),"更新资料成功!");
            else:
                Inc::GoUrl("-1","更新资料失败!");
            endif;
        }
    }
}