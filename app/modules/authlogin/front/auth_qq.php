<?php
namespace Controller\Authlogin\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Authlogin\Authlogin as AuthloginModel;
class Auth_qq{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        $model=new AuthloginModel();
        $auths=$model->GetSet();
        $state=explode("__",Inc::SqlCheck($_GET["state"] ?? ""));
        $code=Inc::SqlCheck($_GET["code"] ?? "");
        if(!empty($code)){
            $thistoken=$model->QqToken($auths,$code);
            $token=Inc::StrSubstr("access_token=","&expires_in=",$thistoken);
            if(!empty($token)){
                $data=$model->QqOpenid($token);
                $openid=$data['openid'] ?? "";
                if(!empty($openid)){
                    $data_r=$model->QqUser($auths,$token,$openid);
                    setcookie("auth_openid",$openid);
                    setcookie("auth_name",$data_r['nickname'] ?? "");
                    setcookie("auth_avatar",$data_r['figureurl_qq_1'] ?? "");
                    if(count($state)>1){
                        Inc::GoUrl(Route::Link($state[0],$state[1]));
                    }else{
                        Inc::GoUrl(Route::Link($state[0]));
                    }
                }else{
                    Inc::GoUrl("-1","OpenId读取失败!");
                }
            }else{
                Inc::GoUrl("-1","Access Token读取失败!");
            }
        }else{
            Inc::GoUrl("-1","Code令牌读取失败!");
        }
    }
}