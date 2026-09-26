<?php
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
$auths=Data::QueryData("cms_connect","","","","1")["querydata"][0];
$state=explode("__",Inc::SqlCheck($_GET["state"]));
$code=Inc::SqlCheck($_GET["code"]);
if(!empty($code)){
    $callback=Inc::HttpPost("https://api.weibo.com/oauth2/access_token?client_id=".$auths["wb_appid"]."&client_secret=".$auths["wb_appkey"]."&grant_type=authorization_code&redirect_uri=".$auths["wb_reurl"]."&code=".$code,"v=UTFrame");
    $data = json_decode($callback, true);
    $token=$data['access_token'];
    $openid=$data['uid'];
    if(!empty($token) && !empty($openid)){
        $callback_r=file_get_contents("https://api.weibo.com/2/users/show.json?uid=".$openid."&access_token=".$token);
        $data_r = json_decode($callback_r, true);
            setcookie("auth_openid",$data['id']); 
            setcookie("auth_name",$data_r['screen_name']); 
            setcookie("auth_avatar",$data_r['profile_image_url']);
            if(count($state)>1){
                Inc::GoUrl("?m=".$state[0]."&p=".$state[1],"");
            }else{
                Inc::GoUrl("?m=".$state[0],"");
            }
    }else{
        Inc::GoUrl("-1","Access Token读取失败!");
    }
}else{
    Inc::GoUrl("-1","Code令牌读取失败!");
}