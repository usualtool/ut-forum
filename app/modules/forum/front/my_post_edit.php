<?php
namespace Controller\Forum\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Forum\Forum as ForumModel;
use Model\Forum\Session as SessionModel;
use Model\Forum\Power as PowerModel;
class My_post_edit{
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
        $id=Inc::SqlCheck($_GET["id"] ?? "");
        $datatree=$model->GetTree();
        $data=$model->GetMyPost($id,$uid);
        $app->Runin(array("webplace","datatree","id","data"),array("编辑主题",$datatree,$id,$data));
        if(($_GET["do"] ?? "")=="edit"){
            $look=Inc::SqlCheck($_POST["look"] ?? "");
            $fid=Inc::SqlCheck($_POST["fid"] ?? "");
            $title=Inc::SqlCheck($_POST["title"] ?? "");
            $content=Inc::SqlCheck($_POST["ut-editor"] ?? "");
            $payfiles=Inc::SqlCheck($_POST["payfiles"] ?? "");
            $files=implode(",",Inc::SqlCheck($_POST["files"] ?? array()));
            if($model->UpdatePost(array(
                "look"=>$look,
                "fid"=>$fid,
                "title"=>$title,
                "content"=>$content,
                "payfiles"=>$payfiles,
                "files"=>$files),"id='$id' and uid='$uid'")):
                    Inc::GoUrl(Route::Link("forum","post","id=".$id),"编辑成功!");
                else:
                    Inc::GoUrl("-1","编辑失败!");
                endif;
        }
        $app->Open("my_post_edit.cms");
    }
}