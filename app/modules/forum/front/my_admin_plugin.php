<?php
require dirname(__FILE__).'/'.'power.php';
require dirname(__FILE__).'/'.'session.php';
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
use library\UsualToolRoute\UTRoute;
if($utype!=99999):
    UTInc::GoUrl("-1","权限不足!"); 
endif;
$t=UTInc::SqlCheck($_GET["t"]);
$do=UTInc::SqlCheck($_GET["do"]);
$app->Runin(array("webplace"),array("插件"));
if(empty($t)):
    $app->Runin("plugin",UTInc::GetPlugin());
elseif($t=="usualtool"):
    $app->Runin("plugin",UTInc::GetPlugin(1));
endif;
$app->Runin("t",$t);
$app->Open("my_admin_plugin.cms");
if($do=="install"){
    $d=$_GET["d"];
    $pid=str_replace(".","",UTInc::SqlCheck($_GET["pid"]));  
    if($d=="usualtool"):
        $down=UTInc::Auth($config["UTCODE"],$config["UTFURL"],"plugin-".$pid);
        $downurl=UTInc::StrSubstr("<downurl>","</downurl>",$down);
        $filename=basename($downurl);
        $res=UTInc::SaveFile($downurl,APP_ROOT."/plugins",$filename,1);
        if(!empty($res)):
            UTInc::Auth($config["UTCODE"],$config["UTFURL"],"plugindel-".str_replace(".zip","",$filename)."");
            $zip=new ZipArchive;
            if($zip->open(APP_ROOT."/plugins/".$filename)===TRUE): 
                $zip->extractTo(APP_ROOT."/plugins/");
                $zip->close();
                unlink(APP_ROOT."/plugins/".$filename);
            else:
               UTInc::GoUrl("-1","plugins目录775权限不足!");
            endif;
        else:
            UTInc::GoUrl("-1","安装权限不足!");
        endif;
    endif; 
    $pconfig=APP_ROOT."/plugins/".$pid."/usualtool.config";
    $plugins=file_get_contents($pconfig);
    $type=UTInc::StrSubstr("<type>","</type>",$plugins);
    $auther=UTInc::StrSubstr("<auther>","</auther>",$plugins);
    $title=UTInc::StrSubstr("<title>","</title>",$plugins);
    $ver=UTInc::StrSubstr("<ver>","</ver>",$plugins);
    $description=UTInc::StrSubstr("<description>","</description>",$plugins);
    $installsql=UTInc::StrSubstr("<installsql><![CDATA[","]]></installsql>",$plugins);
    if(UTData::QueryData("cms_plugin","","pid='$pid'","","1")["querynum"]>0):
        UTData::UpdateData("cms_plugin",array(
            "type"=>$type,
            "auther"=>$auther,
            "title"=>$title,
            "ver"=>$ver,
            "description"=>$description),"pid='$pid'");
    else:
        UTData::InsertData("cms_plugin",array(
            "pid"=>$pid,
            "type"=>$type,
            "auther"=>$auther,
            "title"=>$title,
            "ver"=>$ver,
            "description"=>$description));
    endif;
    if($installsql=='0'):
        UTInc::GoUrl(UTRoute::Link("forum","my_admin_plugin","t=".$d),"成功安装插件!");
    else:
        if(UTData::RunSql($installsql)):
            UTInc::GoUrl(UTRoute::Link("forum","my_admin_plugin","t=".$d),"成功安装插件!");
        else:
            UTInc::GoUrl("-1","插件安装失败!");
        endif;   
    endif;
}
if($do=="uninstall"){
    $d=$_GET["d"];
    $pid=str_replace(".","",UTInc::SqlCheck($_GET["pid"]));
    $pconfig=APP_ROOT."/plugins/".$pid."/usualtool.config";
    $plugins=file_get_contents($pconfig);
    $uninstallsql=UTInc::StrSubstr("<uninstallsql><![CDATA[","]]></uninstallsql>",$plugins);
    UTData::DelData("cms_plugin","pid='$pid'");
    if($uninstallsql=='0'):
        UTInc::DelDir(APP_ROOT."/plugins/".$pid);
        UTInc::GoUrl(UTRoute::Link("forum","my_admin_plugin","t=".$d),"成功卸载插件!");
    else:
        if(UTData::RunSql($uninstallsql)):
            UTInc::DelDir(APP_ROOT."/plugins/".$pid);
            UTInc::GoUrl(UTRoute::Link("forum","my_admin_plugin","t=".$d),"成功卸载插件!");
        else:
            UTInc::GoUrl("-1","插件卸载失败!");
        endif;   
    endif;
}