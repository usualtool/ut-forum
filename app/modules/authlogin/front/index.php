<?php
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
$auth=Inc::SqlCheck($_GET["auth"]);
$state=Inc::SqlCheck($_GET["state"]);
$auths=Data::QueryData("cms_connect","","","","1")["querydata"][0];
if($auth=="qq"){
    Inc::GoUrl("https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=".$auths["qq_appid"]."&state=".$state."&redirect_uri=".URLEncode($auths["qq_reurl"]),"");
}elseif($auth=="weibo"){
    Inc::GoUrl("https://api.weibo.com/oauth2/authorize?client_id=".$auths["wb_appid"]."&state=".$state."&response_type=code&forcelogin=true&redirect_uri=".URLEncode($auths["wb_reurl"]),"");
}elseif($auth=="wechat"){
    if(Inc::IsApp()){    
        Inc::GoUrl("https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$auths["wx_appid"]."&redirect_uri=".URLEncode($auths["wx_reurl"])."&response_type=code&scope=snsapi_userinfo&state=".$state."#wechat_redirect","");
    }else{     
        Inc::GoUrl("https://open.weixin.qq.com/connect/qrconnect?appid=".$auths["wx_appid"]."&redirect_uri=".URLEncode($auths["wx_reurl"])."&response_type=code&scope=snsapi_login&state=".$state,"");
    }
}