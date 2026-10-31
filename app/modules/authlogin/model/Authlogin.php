<?php
namespace Model\Authlogin;
use usualtool\Lib\Data;
use usualtool\Lib\Inc;
class Authlogin{
    public function GetSet(){
        return Data::QueryData("cms_connect","","","","1")["querydata"][0] ?? array();
    }
    public function GetList(){
        return Data::QueryData("cms_connect","","","","1")["querydata"];
    }
    public function Update($data,$where){
        return Data::UpdateData("cms_connect",$data,$where);
    }
    public function QqToken($auths,$code){
        return file_get_contents("https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id=".$auths["qq_appid"]."&client_secret=".$auths["qq_appkey"]."&code=$code&redirect_uri=".$auths["qq_reurl"]);
    }
    public function QqOpenid($token){
        $callback=file_get_contents("https://graph.qq.com/oauth2.0/me?fmt=json&access_token=".$token);
        return json_decode($callback,true);
    }
    public function QqUser($auths,$token,$openid){
        $callback_r=file_get_contents("https://graph.qq.com/user/get_user_info?access_token=".$token."&oauth_consumer_key=".$auths["qq_appid"]."&openid=".$openid);
        return json_decode($callback_r,true);
    }
    public function WechatToken($auths,$code){
        $callback=file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$auths["wx_appid"]."&secret=".$auths["wx_appkey"]."&code=".$code."&grant_type=authorization_code");
        return json_decode($callback,true);
    }
    public function WechatUser($token,$openid){
        $callback_r=file_get_contents("https://api.weixin.qq.com/sns/userinfo?access_token=".$token."&openid=".$openid);
        return json_decode($callback_r,true);
    }
    public function WeiboToken($auths,$code){
        $callback=Inc::HttpPost("https://api.weibo.com/oauth2/access_token?client_id=".$auths["wb_appid"]."&client_secret=".$auths["wb_appkey"]."&grant_type=authorization_code&redirect_uri=".$auths["wb_reurl"]."&code=".$code,"v=UTFrame");
        return json_decode($callback,true);
    }
    public function WeiboUser($openid,$token){
        $callback_r=file_get_contents("https://api.weibo.com/2/users/show.json?uid=".$openid."&access_token=".$token);
        return json_decode($callback_r,true);
    }
}