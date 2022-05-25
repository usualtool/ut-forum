<?php
require dirname(__FILE__).'/'.'session.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
$pid=UTInc::SqlCheck($_GET["pid"]);
$did=intval(UTInc::SqlCheck($_GET["did"]));
$data=UTData::QueryData("forum_post","","id='$pid'","","")["querydata"][0];
$postmoney=$data["payfiles"];
$buynum=library\UsualToolData\UTData::QueryData("forum_payment","","uid='$uid' and pid='$pid'","","")["querynum"];
function download($thisfile){
    $filename=iconv('utf-8','gb2312',basename($thisfile));
    if(!file_exists($thisfile)){
        Header("HTTP/1.1 404 Not Found");
        exit();
    }else{
        $file=fopen($thisfile,"r");
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length: ".filesize($thisfile));
        Header("Content-Disposition: attachment; filename=".UTInc::GetRandomString(8).".".substr(strrchr($filename,'.'),1));
        echo fread($file,filesize($thisfile));
        fclose($file);
        exit();
    }
}
if(!empty($data["files"])):
    $filearr=explode(",",$data["files"]);
    if($did>=count($filearr)):
        UTInc::GoUrl("-1","下载参数错误!");
    else:
        if($postmoney==0 || ($postmoney>0 && $buynum>0)):
            $file=str_replace($config["APPURL"]."/","",$filearr[$did]);
            UTData::UpdateData("forum_post",array("downnum"=>"downnum+1"),"id='$pid'");
            download($file);
        else:
            UTInc::GoUrl("-1","尚未购买该附件!");
        endif;
    endif;

else:
    UTInc::GoUrl("-1","下载错误!");
endif;