<?php
namespace Controller\Forum\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Forum\Forum as ForumModel;
use Model\Forum\Member as MemberModel;
use Model\Forum\Session as SessionModel;
use Model\Forum\Power as PowerModel;
class My_admin_team{
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
        $utype=$session["utype"];
        if($utype!=99999):
            Inc::GoUrl("-1","权限不足!");
        endif;
        $datatree=$model->GetTree();
        $pagelink="";

        $page=empty($_GET["page"]) ? 1 : $_GET["page"];
        $pagenum=5;
        $data=$member->GetTeam($page,$pagenum);
        $querynum=$data["querynum"];
        $querydata=$data["querydata"];
        $totalpage=ceil($querynum/$pagenum);
        $app->Runin("data",$querydata);
        $app->Runin(array("webplace","total","curpage","listnum","pagelink","datatree"),array("用户管理",$totalpage,$page,$pagenum,$pagelink,$datatree));
        $app->Open("my_admin_team.cms");
        if(($_GET["do"] ?? "")=="banzhu"){
            $id=Inc::SqlCheck($_POST["id"] ?? "");
            $utype=Inc::SqlCheck($_POST["utype"] ?? "");
                    if($member->SetUtype($id,$utype)):
                        Inc::GoUrl(Route::Link("forum","my_admin_team"),"变更版主操作成功!");
                    else:
                        Inc::GoUrl("-1","变更版主操作失败!");
                    endif;
        }
        if(($_GET["do"] ?? "")=="admin"){
            $id=Inc::SqlCheck($_GET["id"] ?? "");
            $utype=Inc::SqlCheck($_GET["utype"] ?? "");
            if($utype==0):
                if($member->CountAdmin()==1):
                    Inc::GoUrl("-1","最后一个管理不能取消!");
                endif;
            endif;
                    if($member->SetUtype($id,$utype)):
                        Inc::GoUrl(Route::Link("forum","my_admin_team"),"变更管理操作成功!");
                    else:
                        Inc::GoUrl("-1","变更管理操作失败!");
                    endif;
        }
        if(($_GET["do"] ?? "")=="del"){
            $id=Inc::SqlCheck($_GET["id"] ?? "");
                if($member->Delete($id)):
                    Inc::GoUrl(Route::Link("forum","my_admin_team"),"删除成功!");
                else:
                    Inc::GoUrl("-1","删除失败!");
                endif;
        }
    }
}