<?php
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
$auths=Data::QueryData("cms_connect","","","","1")["querydata"][0];
$state=explode("__",Inc::SqlCheck($_GET["state"]));
$code=Inc::SqlCheck($_GET["code"]);
if(!empty($code)){
    $callback=file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$auths["wx_appid"]."&secret=".$auths["wx_appkey"]."&code=".$code."&grant_type=authorization_code");
    $data = json_decode($callback, true);
    $token=$data['access_token'];
    $openid =$data['openid']; 
    if(!empty($token) && !empty($openid)){
        $callback_r=file_get_contents("https://api.weixin.qq.com/sns/userinfo?access_token=".$token."&openid=".$openid);
        $data_r = json_decode($callback_r, true);
        setcookie("auth_openid",$data_r['openid']); 
        setcookie("auth_name",$data_r['nickname']); 
        setcookie("auth_avatar",$data_r['headimgurl']);
        if(count($state)>1){
            Inc::GoUrl("?m=".$state[0]."&p=".$state[1],"");
        }else{
            Inc::GoUrl("?m=".$state[0],"");
        }
    }else{
        Inc::GoUrl("-1","OpenId读取失败!");
    }
}else{
    Inc::GoUrl("-1","Code令牌读取失败!");
}