<?php
require dirname(__FILE__).'/'.'session.php';
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
$pid=Inc::SqlCheck($_GET["pid"]);
$did=intval(Inc::SqlCheck($_GET["did"]));
$data=Data::QueryData("forum_post","","id='$pid'","","")["querydata"][0];
$postmoney=$data["payfiles"];
$buynum=Data::QueryData("forum_payment","","uid='$uid' and pid='$pid'","","")["querynum"];
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
        Header("Content-Disposition: attachment; filename=".Inc::GetRandomString(8).".".substr(strrchr($filename,'.'),1));
        echo fread($file,filesize($thisfile));
        fclose($file);
        exit();
    }
}
if(!empty($data["files"])):
    $filearr=explode(",",$data["files"]);
    if($did>=count($filearr)):
        Inc::GoUrl("-1","下载参数错误!");
    else:
        if($postmoney==0 || ($postmoney>0 && $buynum>0)):
            $file=str_replace($config["APPURL"]."/","",$filearr[$did]);
            Data::UpdateData("forum_post",array("downnum"=>"downnum+1"),"id='$pid'");
            download($file);
        else:
            Inc::GoUrl("-1","尚未购买该附件!");
        endif;
    endif;

else:
    Inc::GoUrl("-1","下载错误!");
endif;