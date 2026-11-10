<?php
namespace Controller\Payment\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Payment\Pay as PayModel;
class Index{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        $model=new PayModel();
    $uid=empty($_POST["uid"]) ? 0 : ($_POST["uid"] ?? "");
    $form=empty($_POST["form"]) ? 1 : ($_POST["form"] ?? "");
    $posnum=empty($_POST["posnum"]) ? Inc::GetRandomString(14,"0123456789") : ($_POST["posnum"] ?? "");
    $amount=empty($_POST["amount"]) ? 0 : ($_POST["amount"] ?? "");
    $unit=empty($_POST["unit"]) ? "CNY" : ($_POST["unit"] ?? "");
    $remark=empty($_POST["remark"]) ? "" : ($_POST["remark"] ?? "");
    $returnurl=empty($_POST["returnurl"]) ? "" : ($_POST["returnurl"] ?? "");
    $result=$model->AddLog(array(
        "uid"=>$uid,
        "form"=>$form,
        "state"=>0,
        "posnum"=>$posnum,
        "amount"=>$amount,
        "unit"=>$unit,
        "remark"=>$remark,
        "returnurl"=>$returnurl,
        "postime"=>date('Y-m-d H:i:s',time())));  
    if($result){
        if($form==1 || $form=="alipay"){
            Inc::GoUrl(Route::Link("payment", "pay", "lb=alipay&posnum=".$posnum),"");   
        }elseif($form==2 || $form=="wechat"){
            Inc::GoUrl(Route::Link("payment", "pay", "lb=wechat&posnum=".$posnum),"");   
        }elseif($form==3 || $form=="paypal"){
            Inc::GoUrl(Route::Link("payment", "pay", "lb=paypal&posnum=".$posnum),"");
        }
    }else{
        Inc::GoUrl("-1","支付错误,请重试!");
    }
    }
}