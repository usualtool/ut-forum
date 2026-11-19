<?php
namespace Model\Payment;
use usualtool\Lib\Data;
class Pay{
    public function GetList(){
        return Data::QueryData("cms_pay","","","","1")["querydata"] ?? array();
    }
    public function GetSet(){
        return Data::QueryData("cms_pay","","","","1")["querydata"][0] ?? array();
    }
    public function SaveConfig($data,$id=""){
        if(empty($id)){
            return Data::InsertData("cms_pay",$data);
        }
        return Data::UpdateData("cms_pay",$data,"id=?",array(intval($id)));
    }
    public function GetLog($posnum,$limit=""){
        return Data::QueryData("cms_pay_log","","posnum=?","",$limit,array($posnum))["querydata"][0] ?? array();
    }
    public function GetLogPaypal($posnum){
        return Data::QueryData("cms_pay_log","","posnum=?","","1",array($posnum))["querydata"][0] ?? array();
    }
    public function GetAmount($posnum){
        return Data::QueryData("cms_pay_log","amount","posnum=?","","",array($posnum))["querydata"][0]["amount"] ?? "";
    }
    public function GetReturnUrl($posnum){
        return Data::QueryData("cms_pay_log","","posnum=?","","",array($posnum))["querydata"][0]["returnurl"] ?? "";
    }
    public function GetLogPage($minid,$pagenum){
        return Data::QueryData("cms_pay_log","","","postime desc",intval($minid).",".intval($pagenum));
    }
    public function AddLog($data){
        return Data::InsertData("cms_pay_log",$data);
    }
    public function MarkPaid($posnum,$paynum){
        return Data::UpdateData("cms_pay_log",array("state"=>1,"paynum"=>$paynum,"paytime"=>date('Y-m-d H:i:s',time())),"posnum=? and state=0",array($posnum));
    }
    public function DelLog($where,$param=array()){
        return Data::DelData("cms_pay_log",$where,$param);
    }
}