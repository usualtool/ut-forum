<?php
namespace Model\Payment;
use usualtool\Lib\Data;
class Pay{
    /* ---------- 支付通道配置 cms_pay ---------- */
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
        return Data::UpdateData("cms_pay",$data,"id='$id'");
    }
    /* ---------- 支付订单 cms_pay_log ---------- */
    public function GetLog($posnum,$limit=""){
        return Data::QueryData("cms_pay_log","","posnum='$posnum'","",$limit)["querydata"][0] ?? array();
    }
    public function GetLogPaypal($posnum){
        return \library\UsualToolData\Data::QueryData("cms_pay_log","","posnum='$posnum'","","1")["querydata"][0] ?? array();
    }
    public function GetAmount($posnum){
        return Data::QueryData("cms_pay_log","amount","posnum='$posnum'","","")["querydata"][0]["amount"] ?? "";
    }
    public function GetReturnUrl($posnum){
        return Data::QueryData("cms_pay_log","","posnum='$posnum'","","")["querydata"][0]["returnurl"] ?? "";
    }
    public function GetLogPage($minid,$pagenum){
        return Data::QueryData("cms_pay_log","","","postime desc","$minid,$pagenum");
    }
    public function AddLog($data){
        return Data::InsertData("cms_pay_log",$data);
    }
    public function MarkPaid($posnum,$paynum){
        return Data::UpdateData("cms_pay_log",array("state"=>1,"paynum"=>$paynum,"paytime"=>date('Y-m-d H:i:s',time())),"posnum='$posnum' and state=0");
    }
    public function DelLog($where){
        return Data::DelData("cms_pay_log",$where);
    }
    /* ---------- 小程序/公众号基础配置 cms_routine ---------- */
    public function GetRoutine($lang=null){
        if($lang===null){
            return Data::QueryData("cms_routine","","","","1")["querydata"][0] ?? array();
        }
        return Data::QueryData("cms_routine","","","","1",$lang)["querydata"][0] ?? array();
    }
}