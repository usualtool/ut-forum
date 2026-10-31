<?php
namespace Controller\Forum\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Forum\Forum as ForumModel;
use Model\Forum\Session as SessionModel;
use Model\Forum\Power as PowerModel;
class Post_admin{
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
        $id=Inc::SqlCheck($_GET["id"] ?? "");
        $rid=Inc::SqlCheck($_GET["rid"] ?? "");
        $fid=$model->GetPostFid($id);
        if($utype==99999 || $utype==$fid){
            if(($_GET["do"] ?? "")=="ding"){
                $ding=Inc::SqlCheck($_GET["ding"] ?? "");
                    if($model->SetDing($id,$ding)):
                        Inc::GoUrl(Route::Link("forum","post","id=".$id),"置顶状态操作成功!");
                    else:
                        Inc::GoUrl("-1","置顶状态操作失败!");
                    endif;
            }
            if(($_GET["do"] ?? "")=="close"){
                $close=Inc::SqlCheck($_GET["close"] ?? "");
                    if($model->SetClose($id,$close)):
                        Inc::GoUrl(Route::Link("forum","post","id=".$id),"屏蔽状态操作成功!");
                    else:
                        Inc::GoUrl("-1","屏蔽状态操作失败!");
                    endif;
            }
            if(($_GET["do"] ?? "")=="notice"){
                    if($model->SetNotice($id)):
                        Inc::GoUrl(Route::Link("forum","post","id=".$id),"解除公告成功!");
                    else:
                        Inc::GoUrl("-1","解除公告失败!");
                    endif;
            }
            if(($_GET["do"] ?? "")=="del"){
                    if($model->DeletePost($id)):
                        Inc::GoUrl(Route::Link("forum"),"主题删除成功!");
                    else:
                        Inc::GoUrl("-1","主题删除失败!");
                    endif;
            }
            if(($_GET["do"] ?? "")=="delreply"){
                    if($model->DeleteReply($rid)):
                        Inc::GoUrl(Route::Link("forum","post","id=".$id),"回复删除成功!");
                    else:
                        Inc::GoUrl("-1","回复删除失败!");
                    endif;
            }
        }else{
            Inc::GoUrl("-1","权限不足!");
        }
    }
}