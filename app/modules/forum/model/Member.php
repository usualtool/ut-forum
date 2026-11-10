<?php
namespace Model\Forum;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
class Member{
    public function Get($uid){
        return Data::QueryData("forum_member","","id=?","","",array(intval($uid)))["querydata"];
    }
    public function GetByOpenid($openid){
        return Data::QueryData("forum_member","","openid=?","","",array($openid));
    }
    public function GetMoney($uid){
        return Data::QueryData("forum_member","money","id=?","","",array(intval($uid)))["querydata"][0]["money"];
    }
    public function GetNameById($id){
        return Data::QueryData("forum_member","username","id=?","","",array(intval($id)))["querydata"][0]["username"];
    }
    public function GetIdByName($username){
        return Data::QueryData("forum_member","id","username=?","","",array($username))["querydata"][0]["id"];
    }
    public function ExistsId($id){
        return Data::QueryData("forum_member","id","id=?","","",array(intval($id)))["querynum"];
    }
    public function ExistsName($username){
        return Data::QueryData("forum_member","","username=?","","",array($username))["querynum"];
    }
    public function CountNameOrEmail($username,$email){
        $datanum=Data::QueryData("forum_member","","username=? or email=?","","",array($username,$email))["querynum"];
        if(Data::ModTable("cms_member")):
            $ut_datanum=Data::QueryData("cms_member","","username=? or email=?","","",array($username,$email))["querynum"];
            $datanum=$datanum+$ut_datanum;
        endif;
        return $datanum;
    }
    public function Login($username,$password){
        $data=Data::QueryData("forum_member","","(username=? || email=? || telephone=?) and black=0","","",array($username,$username,$username));
        if($data["querynum"]!=1){
            return "nouser";
        }
        $rows=$data["querydata"][0];
        if(!password_verify($password,$rows["password"])){
            return "nopass";
        }
        $this->RecordLogin($rows["id"]);
        return $rows;
    }
    public function RecordLogin($uid){
        Data::UpdateData("forum_member",array("lasttime"=>date('Y-m-d H:i:s',time())),"id=?",array(intval($uid)));
        return Data::InsertData("forum_member_log",array("uid"=>$uid,"ip"=>Inc::GetIp(),"content"=>"login","addtime"=>date('Y-m-d H:i:s',time())));
    }
    public function GetLogs($uid,$size=5){
        return Data::QueryData("forum_member_log","","uid=?","addtime desc","0,$size",array(intval($uid)))["querydata"];
    }
    public function Register($username,$passwordx,$avatar,$email,$fullname,$sex){
        $newid=Data::InsertData("forum_member",array(
            "username"=>$username,
            "password"=>$passwordx,
            "avatar"=>$avatar,
            "email"=>$email,
            "fullname"=>$fullname,
            "sex"=>$sex,
            "creattime"=>date('Y-m-d H:i:s',time())));
        if($newid):
            if(Data::ModTable("cms_member")):
                Data::InsertData("cms_member",array(
                    "username"=>$username,
                    "password"=>$passwordx,
                    "balance"=>0,
                    "avatar"=>$avatar,
                    "email"=>$email,
                    "fullname"=>$fullname,
                    "sex"=>$sex,
                    "creattime"=>date('Y-m-d H:i:s',time())));
            endif;
        endif;
        return $newid;
    }
    public function AddOpenid($openid,$avatar,$username){
        return Data::InsertData("forum_member",array(
            "openid"=>$openid,
            "avatar"=>$avatar,
            "username"=>$username,
            "password"=>"0",
            "salts"=>"0",
            "creattime"=>date('Y-m-d H:i:s',time())));
    }
    public function SetBlack($username,$black){
        return Data::UpdateData("forum_member",array("black"=>$black),"username=?",array($username));
    }
    public function GetBlackList(){
        return Data::QueryData("forum_member","","black=1")["querydata"];
    }
    public function Update($data,$where,$param=array()){
        return Data::UpdateData("forum_member",$data,$where,$param);
    }
    public function SetUtype($id,$utype){
        return Data::UpdateData("forum_member",array("utype"=>$utype),"id=?",array(intval($id)));
    }
    public function GetTeam($page=1,$size=5){
        $minid=$size*($page-1);
        return Data::QueryData("forum_member","","","utype desc,creattime desc","$minid,$size");
    }
    public function CountAdmin(){
        return Data::QueryData("forum_member","","utype='99999'","","")["querynum"];
    }
    public function Delete($id){
        return Data::DelData("forum_member","id=?",array(intval($id)));
    }
}