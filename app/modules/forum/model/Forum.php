<?php
namespace Model\Forum;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
class Forum{
    public function GetSet(){
        return Data::QueryData("forum_set","","","","1")["querydata"];
    }
    public function GetCookieName(){
        return Data::QueryData("forum_set","","","","1")["querydata"][0]["cookname"];
    }
    public function UpdateSet($data,$where){
        return Data::UpdateData("forum_set",$data,$where);
    }
    public function GetNotice(){
        return Data::QueryData("forum_post","","close=0 and notice=1","posttime desc","")["querydata"];
    }
    public function GetForums(){
        return Data::QueryData("forum","","bid=0","forum_number desc","")["querydata"];
    }
    public function GetNewUsers(){
        return Data::QueryData("forum_member","","","creattime desc","0,10")["querydata"];
    }
    public function GetNewTopics($size=5){
        return Data::QueryData("forum_post","","close=0 and notice=0","posttime desc","0,".$size)["querydata"];
    }
    public function GetHotTopics($size=10){
        return Data::QueryData("forum_post","","close=0 and notice=0","hit desc","0,".$size)["querydata"];
    }
    public function GetTopStars(){
        return Data::QueryData("forum_member","","id in (Select uid from forum_post group by uid order by count(id) desc)","","0,3")["querydata"];
    }
    public function GetPosts($page=1,$size=10){
        $minid=$size*($page-1);
        return Data::QueryData("forum_post","","close=0 and notice=0","ding desc,posttime desc","$minid,$size");
    }
    public function GetForumPosts($fid,$page=1,$size=10){
        $minid=$size*($page-1);
        return Data::QueryData("forum_post","","fid='$fid' and close=0 and notice=0","ding desc,posttime desc","$minid,$size");
    }
    public function GetClosedPosts($page=1,$size=10){
        $minid=$size*($page-1);
        return Data::QueryData("forum_post","","close=1","posttime desc","$minid,$size");
    }
    public function GetMemberPosts($where,$page=1,$size=10){
        $minid=$size*($page-1);
        return Data::QueryData("forum_post","",$where,"posttime desc","$minid,$size");
    }
    public function SearchPosts($key,$page=1,$size=10){
        $minid=$size*($page-1);
        return Data::QueryData("forum_post","","title like '%$key%' or content like '%$key%'","posttime desc","$minid,$size");
    }
    public function GetSearchTags(){
        return Data::QueryData("cms_search","hit,keyword","","hit desc","20")["querydata"];
    }
    public function GetForumName($id){
        return Data::QueryData("forum","forum_name","id='$id'","","")["querydata"][0]["forum_name"];
    }
    public function GetForumRow($id){
        return Data::QueryData("forum","","id='$id'","","")["querydata"];
    }
    public function GetTree(){
        $datatree=array();
        foreach(Data::QueryData("forum","","","forum_number desc","")["querydata"] as $value){
            $datatree[$value['id']]=array(
                'id'=>$value['id'],
                'bid'=>$value['bid'],
                'name'=>$value['forum_name']
            );
        }
        return $datatree;
    }
    public function AddForum($data){
        return Data::InsertData("forum",$data);
    }
    public function UpdateForum($data,$where){
        return Data::UpdateData("forum",$data,$where);
    }
    public function DeleteForum($id){
        return Data::DelData("forum","id='$id'");
    }
    public function GetPost($id){
        return Data::QueryData("forum_post","","id='$id'","","")["querydata"];
    }
    public function GetMyPost($id,$uid){
        return Data::QueryData("forum_post","","id='$id' and uid='$uid'","","")["querydata"];
    }
    public function GetPostFid($id){
        return Data::QueryData("forum_post","fid","id='$id'","","")["querydata"][0]["fid"];
    }
    public function AddPost($data){
        return Data::InsertData("forum_post",$data);
    }
    public function UpdatePost($data,$where){
        return Data::UpdateData("forum_post",$data,$where);
    }
    public function DeletePost($id){
        return Data::DelData("forum_post","id='$id'");
    }
    public function DeleteMemberPost($uid,$id){
        return Data::DelData("forum_post","uid='$uid' and id='$id'");
    }
    public function AddHit($id){
        return Data::UpdateData("forum_post",array("hit"=>"hit+1"),"id='$id'");
    }
    public function AddDown($id){
        return Data::UpdateData("forum_post",array("downnum"=>"downnum+1"),"id='$id'");
    }
    public function SetDing($id,$ding){
        return Data::UpdateData("forum_post",array("ding"=>$ding),"id='$id'");
    }
    public function SetClose($id,$close){
        return Data::UpdateData("forum_post",array("close"=>$close),"id='$id'");
    }
    public function SetNotice($id){
        return Data::UpdateData("forum_post",array("notice"=>0),"id='$id'");
    }
    public function GetMyReplyNum($id,$uid){
        $my_rnum=Data::QueryData("forum_reply","","postid='$id' and uid='$uid'","","")["querynum"];
        $my_pnum=Data::QueryData("forum_post","","id='$id' and uid='$uid'","","")["querynum"];
        return $my_rnum+$my_pnum;
    }
    public function GetReplies($where,$page=1,$size=5){
        $minid=$size*($page-1);
        return Data::QueryData("forum_reply","",$where,"replytime asc","$minid,$size");
    }
    public function AddReply($data){
        return Data::InsertData("forum_reply",$data);
    }
    public function DeleteReply($id){
        return Data::DelData("forum_reply","id='$id'");
    }
    public function BuyFiles($id,$uid,$postmoney,$postuid,$money){
        if(round($money,2)<round($postmoney,2)){
            return false;
        }
        $jianmoney=round($money,2)-round($postmoney,2);
        Data::InsertData("forum_payment",array(
            "types"=>3,
            "state"=>1,
            "posnum"=>Inc::GetRandomString(14,"123456789"),
            "uid"=>$uid,
            "pid"=>$id,
            "amount"=>$postmoney,
            "addtime"=>date('Y-m-d H:i:s',time())));
        if(Data::UpdateData("forum_member",array("money"=>$jianmoney),"id='$uid'")):
            $postuidmoney=Data::QueryData("forum_member","money","id='$postuid'","","")["querydata"][0]["money"];
            $jiamoney=round($postuidmoney,2)+round($postmoney,2);
            Data::InsertData("forum_payment",array(
                "types"=>2,
                "state"=>1,
                "posnum"=>Inc::GetRandomString(14,"987654321"),
                "uid"=>$postuid,
                "pid"=>$id,
                "amount"=>$postmoney,
                "addtime"=>date('Y-m-d H:i:s',time())));
            Data::UpdateData("forum_member",array("money"=>$jiamoney),"id='$postuid'");
        endif;
        return true;
    }
    public function AddComment($data){
        return Data::InsertData("forum_comment",$data);
    }
    public function AddReport($data){
        return Data::InsertData("forum_report",$data);
    }
    public function GetReports($page=1,$size=10){
        $minid=$size*($page-1);
        return Data::QueryData("forum_report","","","addtime desc","$minid,$size");
    }
    public function DeleteReport($id){
        return Data::DelData("forum_report","id='$id'");
    }
    public function GetMessages($where,$page=1,$size=10){
        $minid=$size*($page-1);
        return Data::QueryData("forum_message","",$where,"sendtime desc","$minid,$size");
    }
    public function AddMessage($data){
        return Data::InsertData("forum_message",$data);
    }
    public function GetPayments($where,$page=1,$size=10){
        $minid=$size*($page-1);
        return Data::QueryData("forum_payment","",$where,"addtime desc","$minid,$size");
    }
    public function AddPayment($data){
        return Data::InsertData("forum_payment",$data);
    }
    public function GetPaymentState($posnum){
        return Data::QueryData("forum_payment","state","posnum='$posnum'","","")["querydata"][0]["state"];
    }
    public function SetPaymentState($posnum){
        return Data::UpdateData("forum_payment",array("state"=>1),"posnum='$posnum'");
    }
    public function CountBuy($uid,$pid){
        return Data::QueryData("forum_payment","","uid='$uid' and pid='$pid'","","")["querynum"];
    }
    public function GetPayLog($posnum){
        return Data::QueryData("cms_pay_log","","posnum='$posnum'","","")["querydata"][0];
    }
    public function GetPayConfig(){
        return Data::QueryData("cms_pay","","","","1")["querydata"];
    }
    public function GetConnectConfig(){
        return Data::QueryData("cms_connect","","","","1")["querydata"];
    }
    public function SavePay($id,$l,$data){
        $sql=0;
        if(!empty($id)){
            if($l=="alipay"):
                $sql=Data::UpdateData("cms_pay",array(
                    "ali_appid"=>$data["ali_appid"],
                    "ali_private_key"=>$data["ali_private_key"],
                    "ali_public_key"=>$data["ali_public_key"],
                    "ali_notify_url"=>$data["ali_notify_url"],
                    "ali_return_url"=>$data["ali_return_url"]),"id='$id'");
            elseif($l=="wechat"):
                $sql=Data::UpdateData("cms_pay",array(
                    "wx_appid"=>$data["wx_appid"],
                    "wx_mchid"=>$data["wx_mchid"],
                    "wx_key"=>$data["wx_key"],
                    "wx_secert"=>$data["wx_secert"],
                    "wx_notify_url"=>$data["wx_notify_url"]),"id='$id'");
            elseif($l=="paypal"):
                $sql=Data::UpdateData("cms_pay",array(
                    "pp_mod"=>$data["pp_mod"],
                    "pp_clientid"=>$data["pp_clientid"],
                    "pp_secret"=>$data["pp_secret"],
                    "pp_return_url"=>$data["pp_return_url"],
                    "pp_notify_url"=>$data["pp_notify_url"]),"id='$id'");
            elseif($l=="url"):
                $sql=Data::UpdateData("cms_pay",array(
                    "url_ok"=>$data["url_ok"],
                    "url_no"=>$data["url_no"]),"id='$id'");
            endif;
        }else{
            if($l=="alipay"):
                $sql=Data::InsertData("cms_pay",array(
                    "ali_appid"=>$data["ali_appid"],
                    "ali_private_key"=>$data["ali_private_key"],
                    "ali_public_key"=>$data["ali_public_key"],
                    "ali_notify_url"=>$data["ali_notify_url"],
                    "ali_return_url"=>$data["ali_return_url"]));
            elseif($l=="wechat"):
                $sql=Data::InsertData("cms_pay",array(
                    "wx_appid"=>$data["wx_appid"],
                    "wx_mchid"=>$data["wx_mchid"],
                    "wx_key"=>$data["wx_key"],
                    "wx_secert"=>$data["wx_secert"],
                    "wx_notify_url"=>$data["wx_notify_url"]));
            elseif($l=="paypal"):
                $sql=Data::InsertData("cms_pay",array(
                    "pp_mod"=>$data["pp_mod"],
                    "pp_clientid"=>$data["pp_clientid"],
                    "pp_secret"=>$data["pp_secret"],
                    "pp_return_url"=>$data["pp_return_url"],
                    "pp_notify_url"=>$data["pp_notify_url"]));
            elseif($l=="url"):
                $sql=Data::InsertData("cms_pay",array(
                    "url_ok"=>$data["url_ok"],
                    "url_no"=>$data["url_no"]));
            endif;
        }
        return $sql;
    }
    public function SaveConnect($id,$c,$data){
        if($c=="qq"):
            Data::UpdateData("cms_connect",array("qq_appid"=>$data["qq_appid"],"qq_appkey"=>$data["qq_appkey"],"qq_reurl"=>$data["qq_reurl"]),"id='$id'");
        endif;
        if($c=="weibo"):
            Data::UpdateData("cms_connect",array("wb_appid"=>$data["wb_appid"],"wb_appkey"=>$data["wb_appkey"],"wb_reurl"=>$data["wb_reurl"]),"id='$id'");
        endif;
        if($c=="wechat"):
            Data::UpdateData("cms_connect",array("wx_appid"=>$data["wx_appid"],"wx_appkey"=>$data["wx_appkey"],"wx_reurl"=>$data["wx_reurl"]),"id='$id'");
        endif;
    }
}