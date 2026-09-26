<?php
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
$auths=Data::QueryData("cms_connect","","","","1")["querydata"][0];
$state=explode("__",Inc::SqlCheck($_GET["state"]));
$code=Inc::SqlCheck($_GET["code"]);
if(!empty($code)){
    $thistoken=file_get_contents("https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id=".$auths["qq_appid"]."&client_secret=".$auths["qq_appkey"]."&code=$code&redirect_uri=".$auths["qq_reurl"]);
    $token=Inc::StrSubstr("access_token=","&expires_in=",$thistoken);
    if(!empty($token)){
        $callback=file_get_contents("https://graph.qq.com/oauth2.0/me?fmt=json&access_token=".$token);
        $data = json_decode($callback, true);
        $openid=$data['openid'];
        if(!empty($openid)){
            $callback_r=file_get_contents("https://graph.qq.com/user/get_user_info?access_token=".$token."&oauth_consumer_key=".$auths["qq_appid"]."&openid=".$openid);
            $data_r = json_decode($callback_r, true);
            setcookie("auth_openid",$openid); 
            setcookie("auth_name",$data_r['nickname']); 
            setcookie("auth_avatar",$data_r['figureurl_qq_1']);
            if(count($state)>1){
                Inc::GoUrl("?m=".$state[0]."&p=".$state[1],"");
            }else{
                Inc::GoUrl("?m=".$state[0],"");
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