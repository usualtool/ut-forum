<?php
namespace Controller\Forum\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Forum\Forum as ForumModel;
use Model\Forum\Session as SessionModel;
use Model\Forum\Power as PowerModel;
class My_post_creat{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $app;
        $model=new ForumModel();
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
        if(!empty($_GET["fid"])){
            $app->Runin("fid",Inc::SqlCheck($_GET["fid"] ?? ""));
        }else{
            $app->Runin("fid",0);
        }
        $datatree=$model->GetTree();
        $app->Runin(array("webplace","datatree"),array("撰写主题",$datatree));
        $app->Open("my_post_creat.cms");
        if(($_GET["do"] ?? "")=="creat"){
            if(empty($_POST["ut-editor"])):
                Inc::GoUrl("-1","内容不能为空!");
            endif;
            $fid=Inc::SqlCheck($_POST["fid"] ?? "");
            if(!empty($_POST["notice"])):
                $notice=Inc::SqlCheck($_POST["notice"] ?? "");
            else:
                $notice=0;
            endif;
            $look=Inc::SqlCheck($_POST["look"] ?? "");
            $title=Inc::SqlCheck($_POST["title"] ?? "");
            $content=Inc::SqlCheck($_POST["ut-editor"] ?? "");
            $payfiles=Inc::SqlCheck($_POST["payfiles"] ?? "");
            $files=implode(",",Inc::SqlCheck($_POST["files"] ?? array()));
                    $pid=$model->AddPost(array(
                    "uid"=>$uid,
                    "fid"=>$fid,
                    "notice"=>$notice,
                    "look"=>$look,
                    "title"=>$title,
                    "content"=>$content,
                    "payfiles"=>$payfiles,
                    "files"=>$files,
                    "ip"=>Inc::GetIp(),
                    "posttime"=>date('Y-m-d H:i:s',time())));
                    if($pid):
                        Inc::GoUrl(Route::Link("forum","post","id=".$pid),"撰写成功!");
                    else:
                        Inc::GoUrl("-1","撰写失败!");
                    endif;
        }
    }
}