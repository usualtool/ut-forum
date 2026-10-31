<?php
namespace Controller\Forum\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Forum\Forum as ForumModel;
use Model\Forum\Member as MemberModel;
use Model\Forum\Session as SessionModel;
use Model\Forum\Power as PowerModel;
class My_message{
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
        if(!empty($_GET["reveuid"])){
            $revid=Inc::SqlCheck($_GET["reveuid"] ?? "");
            if($member->ExistsId($revid)==0):
                Inc::GoUrl("-1","接收账户不存在!");
            else:
                $app->Runin("reveuid",$member->GetNameById($revid));
            endif;
        }else{
            $app->Runin("reveuid","");
        }
        $app->Runin("webplace","发信");
        $app->Open("my_message.cms");
        if(($_GET["do"] ?? "")=="creat"){
            $reve=Inc::SqlCheck($_POST["reve"] ?? "");
            $content=Inc::SqlCheck($_POST["ut-editor"] ?? "");
                if($member->ExistsName($reve)==0):
                    Inc::GoUrl("-1","接收方账户不存在!");
                else:
                    $reveuid=$member->GetIdByName($reve);
                endif;
                    $pid=$model->AddMessage(array(
                    "senduid"=>$uid,
                    "reveuid"=>$reveuid,
                    "content"=>$content,
                    "sendtime"=>date('Y-m-d H:i:s',time())));
                    if($pid):
                        Inc::GoUrl(Route::Link("forum","my_sendbox"),"发信成功!");
                    else:
                        Inc::GoUrl("-1","发信失败!");
                    endif;
        }
    }
}