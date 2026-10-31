<?php
namespace Controller\Forum\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Forum\Forum as ForumModel;
use Model\Forum\Session as SessionModel;
class Post{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $app;
        $model=new ForumModel();
        $session=(new SessionModel())->Check();
        $app->Runin(
            array("rate","user","uid","utype","username","money","avatar"),
            array($session["rate"],$session["user"],$session["uid"],$session["utype"],$session["username"],$session["money"],$session["avatar"])
        );
        $uid=$session["uid"];
        $money=$session["money"];
        $id=Inc::SqlCheck($_GET["id"] ?? "");
        $rid=Inc::SqlCheck($_GET["rid"] ?? "");
        $lookuid=Inc::SqlCheck($_GET["uid"] ?? "");
        if(!empty($uid)):
            $my_replynum=$model->GetMyReplyNum($id,$uid);
        else:
            $my_replynum=0;
        endif;
        $model->AddHit($id);
        $data=$model->GetPost($id);
        if($data[0]["close"]==1){
            $title="主题已被屏蔽";
        }else{
            $title=$data[0]["title"];
        }
        if(!empty($lookuid)):
            $pagelink="?&id=".$id."&uid=".$lookuid;

            $where="postid='$id' and uid='$lookuid'";
        else:
            $pagelink="?&id=".$id;

            $where="postid='$id'";
        endif;
        $page=empty($_GET["page"]) ? 1 : $_GET["page"];
        $pagenum=5;
        $data_reply=$model->GetReplies($where,$page,$pagenum);
        $querynum=$data_reply["querynum"];
        $querydata=$data_reply["querydata"];
        $totalpage=ceil($querynum/$pagenum);
        $app->Runin("data_reply",$querydata);
        $app->Runin(array("total","curpage","listnum","pagelink"),array($totalpage,$page,$pagenum,$pagelink));
        $app->Runin(array("webplace","data","rid","my_replynum"),array($title,$data,$rid,$my_replynum));
        $app->Open("post.cms");
        if(($_GET["do"] ?? "")=="reply"){
            if(empty($_POST["ut-editor"])):
                Inc::GoUrl("-1","内容不能为空!");
            endif;
            $page=Inc::SqlCheck($_POST["page"] ?? "");
            $postid=Inc::SqlCheck($_POST["postid"] ?? "");
            $replyid=Inc::SqlCheck($_POST["replyid"] ?? "");
            $content=Inc::SqlCheck($_POST["ut-editor"] ?? "");
            $rid=$model->AddReply(array(
                    "uid"=>$uid,
                    "postid"=>$postid,
                    "replyid"=>$replyid,
                    "content"=>$content,
                    "ip"=>Inc::GetIp(),
                    "replytime"=>date('Y-m-d H:i:s',time())));
                    if($rid):
                        Inc::GoUrl(Route::Link("forum","post","id=".$postid."&page=".$page)."#end","回复主题成功!");
                    else:
                        Inc::GoUrl("-1","回复主题失败!");
                    endif;
        }
        if(($_GET["do"] ?? "")=="payfiles"){
            $postmoney=$data[0]["payfiles"];
            $postuid=$data[0]["uid"];
            if(!$model->BuyFiles($id,$uid,$postmoney,$postuid,$money)):
                Inc::GoUrl("-1","积分不足!");
            endif;
            Inc::GoUrl(Route::Link("forum","post","id=".$id),"购买附件成功!");
        }
        if(($_GET["do"] ?? "")=="file_comment"){
            $content=Inc::SqlCheck($_POST["file_content"] ?? "");
            if(empty($content)):
                Inc::GoUrl("-1","评语不能为空!");
            else:
                $model->AddComment(array(
                    "types"=>2,
                    "postid"=>$id,
                    "uid"=>$uid,
                    "content"=>$content,
                    "addtime"=>date('Y-m-d H:i:s',time())));
                Inc::GoUrl(Route::Link("forum", "post", "id=".$id."&"."page=1#filecomment"),"附件评价成功!");
            endif;
        }
        if(($_GET["do"] ?? "")=="report"){
            $ptype=Inc::SqlCheck($_POST["ptype"] ?? "");
            $postid=Inc::SqlCheck($_POST["postid"] ?? "");
            $content=Inc::SqlCheck($_POST["file_content"] ?? "");
            if($model->AddReport(array(
                "ptype"=>$ptype,
                "postid"=>$postid,
                "uid"=>$uid,
                "content"=>$content,
                "addtime"=>date('Y-m-d H:i:s',time())))):
                Inc::GoUrl("-1","举报完毕!");
            else:
                Inc::GoUrl("-1","举报失败!");
            endif;
        }
    }
}