<?php
namespace Controller\Forum\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Forum\Forum as ForumModel;
use Model\Forum\Session as SessionModel;
use Model\Forum\Power as PowerModel;
class My_admin_forum{
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
        $utype=$session["utype"];
        if($utype!=99999):
            Inc::GoUrl("-1","权限不足!");
        endif;
        $do=$_GET["do"] ?? "";
        $datatree=$model->GetTree();
        if($do=="mon"){
            $id=Inc::SqlCheck($_GET["id"] ?? "");
            $app->Runin("id",$id);
            $app->Runin("data",$model->GetForumRow($id));
        }
        $app->Runin(array("webplace","do","datatree"),array("版块管理",$do,$datatree));
        $app->Open("my_admin_forum.cms");
        if($do=="del"){
            $id=Inc::SqlCheck($_GET["id"] ?? "");
            if($model->DeleteForum($id)){
                Inc::GoUrl(Route::Link("forum","my_admin_forum"),"版块删除成功!");
            }else{
                Inc::GoUrl("-1","版块删除失败!");
            }
        }
        if($do=="a"){
            if($model->AddForum(array(
                "bid"=>Inc::SqlCheck($_POST["bid"] ?? ""),
                "forum_name"=>Inc::SqlCheck($_POST["forum_name"] ?? ""),
                "forum_number"=>Inc::SqlCheck($_POST["forum_number"] ?? ""),
                "forum_content"=>Inc::SqlCheck($_POST["forum_content"] ?? "")))){
                Inc::GoUrl(Route::Link("forum","my_admin_forum"),"版块创建成功!");
            }else{
                Inc::GoUrl("-1","版块创建失败!");
            }
        }
        if($do=="m"){
            $id=Inc::SqlCheck($_POST["id"] ?? "");
            if($model->UpdateForum(array(
                "bid"=>Inc::SqlCheck($_POST["bid"] ?? ""),
                "forum_name"=>Inc::SqlCheck($_POST["forum_name"] ?? ""),
                "forum_number"=>Inc::SqlCheck($_POST["forum_number"] ?? ""),
                "forum_content"=>Inc::SqlCheck($_POST["forum_content"] ?? "")),"id='$id'")){
                Inc::GoUrl(Route::Link("forum","my_admin_forum"),"版块编辑成功!");
            }else{
                Inc::GoUrl("-1","版块编辑失败!");
            }
        }
    }
}