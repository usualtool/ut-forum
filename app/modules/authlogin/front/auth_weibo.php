<?php
namespace Controller\Authlogin\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Authlogin\Authlogin as AuthloginModel;
class Auth_weibo{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        $model=new AuthloginModel();
        $auths=$model->GetSet();
        $state=explode("__",Inc::SqlCheck($_GET["state"] ?? ""));
        $code=Inc::SqlCheck($_GET["code"] ?? "");
        if(!empty($code)){
            $data=$model->WeiboToken($auths,$code);
            $token=$data['access_token'] ?? "";
            $openid=$data['uid'] ?? "";
            if(!empty($token) && !empty($openid)){
                $data_r=$model->WeiboUser($openid,$token);
                setcookie("auth_openid",$data['id'] ?? "");
                setcookie("auth_name",$data_r['screen_name'] ?? "");
                setcookie("auth_avatar",$data_r['profile_image_url'] ?? "");
                if(count($state)>1){
                    Inc::GoUrl(Route::Link($state[0],$state[1]));
                }else{
                    Inc::GoUrl(Route::Link($state[0]));
                }
            }else{
                Inc::GoUrl("-1","Access Token读取失败!");
            }
        }else{
            Inc::GoUrl("-1","Code令牌读取失败!");
        }
    }
}