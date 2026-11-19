<?php
namespace Controller\Forum\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Forum\Forum as ForumModel;
use Model\Forum\Member as MemberModel;
use Model\Forum\Session as SessionModel;
use Model\Forum\Power as PowerModel;
class My{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $app;
        $model=new ForumModel();
        $member=new MemberModel();
        $power=(new PowerModel())->Check();
        if(empty($power["login"])):
            Inc::GoUrl(Route::Link("forum","login","goto=".urlencode(base64_encode(Inc::CurPageUrl()))),"请登陆!");
        endif;
        $session=(new SessionModel())->Check();
        $app->Runin(
            array("rate","user","uid","utype","username","money","avatar"),
            array($session["rate"],$session["user"],$session["uid"],$session["utype"],$session["username"],$session["money"],$session["avatar"])
        );
        $uid=$session["uid"];
        $loginlog=$member->GetLogs($uid);
        $app->Runin(array("webplace","loginlog"),array("我的",$loginlog));
        $app->Open("my.cms");
        if(($_GET["do"] ?? "")=="payment"){
            if(!file_exists(UTF_ROOT.'/vendor/usualtool/ut-pay')){
                http_response_code(503);
                header('Content-Type: text/html; charset=utf-8');
                echo'<pre style="padding:24px;font:14px/1.8 monospace">';
                    echo'缺少 usualtool/ut-pay 依赖<br/>';
                    echo'https://github.com/usualtool/ut-pay';
                echo'</pre>';
                exit(1);
            }
            $paytype=($_POST["paytype"] ?? "");
            $posnum=Inc::GetRandomString(14,"123456789");
            $amount=($_POST["amount"] ?? "");
            $remark="forum-|-".$uid."-|-Recharge/UT/1-|-".$amount;
            if($model->AddPayment(array(
                "state"=>0,
                "posnum"=>$posnum,
                "uid"=>$uid,
                "pid"=>0,
                "amount"=>$amount,
                "addtime"=>date('Y-m-d H:i:s',time())))){
                 echo"<form style='display:none;' name='pay' method='post' action='".Route::Link("payment")."'>
              <input name='form' type='hidden' value='{$paytype}'>
              <input name='uid' type='hidden' value='{$uid}'>
              <input name='posnum' type='hidden' value='{$posnum}'>
              <input name='amount' type='hidden' value='{$amount}'>
              <input name='remark' type='hidden' value='{$remark}'>
              <input name='returnurl' type='hidden' value='".Route::Link("forum","pay")."'>
              </form>
              <script type='text/javascript'>
              function load_submit(){document.pay.submit()}load_submit();
              </script>";
            }else{
                Inc::GoUrl("-1","创建订单失败!请重试!");
            }
        }
    }
}