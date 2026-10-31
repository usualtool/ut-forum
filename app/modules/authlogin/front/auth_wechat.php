<?php
namespace Controller\Authlogin\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Authlogin\Authlogin as AuthloginModel;
class Auth_wechat{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        $model=new AuthloginModel();
        $auths=$model->GetSet();
        $state=explode("__",Inc::SqlCheck($_GET["state"] ?? ""));
        $code=Inc::SqlCheck($_GET["code"] ?? "");
        if(!empty($code)){
            $data=$model->WechatToken($auths,$code);
            $token=$data['access_token'] ?? "";
            $openid=$data['openid'] ?? "";
            if(!empty($token) && !empty($openid)){
                $data_r=$model->WechatUser($token,$openid);
                setcookie("auth_openid",$data_r['openid'] ?? "");
                setcookie("auth_name",$data_r['nickname'] ?? "");
                setcookie("auth_avatar",$data_r['headimgurl'] ?? "");
                if(count($state)>1){
                    Inc::GoUrl(Route::Link($state[0],$state[1]));
                }else{
                    Inc::GoUrl(Route::Link($state[0]));
                }
            }else{
                Inc::GoUrl("-1","OpenId读取失败!");
            }
        }else{
            Inc::GoUrl("-1","Code令牌读取失败!");
        }
    }
}