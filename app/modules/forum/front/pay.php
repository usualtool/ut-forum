<?php
namespace Controller\Forum\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Forum\Forum as ForumModel;
use Model\Forum\Member as MemberModel;
use Model\Forum\Session as SessionModel;
class Pay{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $app;
        $model=new ForumModel();
        $member=new MemberModel();
        $session=(new SessionModel())->Check();
        $app->Runin(
            array("rate","user","uid","utype","username","money","avatar"),
            array($session["rate"],$session["user"],$session["uid"],$session["utype"],$session["username"],$session["money"],$session["avatar"])
        );
        $set=$session["set"];
        $posnum=empty($_GET["posnum"]) ? Inc::SqlCheck($_POST["posnum"] ?? "") : Inc::SqlCheck($_GET["posnum"] ?? "");
        if(!empty($posnum)){
            $data=$model->GetPayLog($posnum);
            $uid=$data["uid"];
            //社区整合数据
            $state=$model->GetPaymentState($posnum);
            $amount=$data["amount"]*$set[0]["rate"];
            $money=$member->GetMoney($uid);
            $newmoney=round($money,2)+round($amount,2);
        }
        if(!empty($_GET["posnum"])){
            if($data["state"]==1 && $state==0){
                $member->Update(array("money"=>$newmoney),"id='$uid'");
                $model->SetPaymentState($posnum);
                Inc::GoUrl(Route::Link("forum","my"),"充值成功!");
            }else{
                Inc::GoUrl("-1","充值失败!");
            }
        }
        if(!empty($_POST["posnum"])){
            if($data["state"]==1 && $state==0){
                $member->Update(array("money"=>$newmoney),"id='$uid'");
                $model->SetPaymentState($posnum);
            }
        }
    }
}