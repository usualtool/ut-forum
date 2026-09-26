<?php
require dirname(__FILE__).'/'.'power.php';
require dirname(__FILE__).'/'.'session.php';
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
if($utype!=99999):
    Inc::GoUrl("-1","权限不足!"); 
endif;
$t=$_GET["t"];
$do=$_GET["do"];
$app->Runin(array("webplace"),array("插件"));
$data=Inc::GetPlugin(1);
if(empty($t)):
    $plugin = array_filter($data, function($item) {
        $pid = (string)($item['pid'] ?? '');
        return substr($pid, 0, 2) === 'f_';
    });
    $plugin = array_values($plugin);
else:
    $plugin = array_filter($data, function($item) {
        $pid = (string)($item['pid'] ?? '');
        return substr($pid, 0, 2) !== 'f_';
    });
    $plugin = array_values($plugin);
endif;
$app->Runin("plugin",$plugin);
$app->Runin("t",$t);
$app->Open("my_admin_plugin.cms");
if($do=="install"){
    $d=$_GET["d"];
    $pid=str_replace(".","",Inc::SqlCheck($_GET["pid"]));
    $down=Inc::Auth($config["UTCODE"],$config["UTFURL"],"plugin-".$pid);
    $downurl=Inc::StrSubstr("<downurl>","</downurl>",$down);
    $filename=basename($downurl);
    $res=Inc::SaveFile($downurl,APP_ROOT."/plugins",$filename,1);
    if(!empty($res)):
        Inc::Auth($config["UTCODE"],$config["UTFURL"],"plugindel-".str_replace(".zip","",$filename)."");
        $zip=new ZipArchive;
        if($zip->open(APP_ROOT."/plugins/".$filename)===TRUE): 
            $zip->extractTo(APP_ROOT."/plugins/");
            $zip->close();
            unlink(APP_ROOT."/plugins/".$filename);
        else:
           Inc::GoUrl("-1","plugins目录775权限不足!");
        endif;
    else:
        Inc::GoUrl("-1","安装权限不足!");
    endif;
    if(is_dir(APP_ROOT."/plugins/".$pid."/assets")):
		    $assets_dir=OPEN_ROOT."/assets/plugins/".$pid;
        if(!is_dir($assets_dir)):
			      Inc::MakeDir($assets_dir);
		    endif;
		    Inc::MoveDir(APP_ROOT."/plugins/".$pid."/assets",$assets_dir);
				Inc::DelDir(APP_ROOT."/plugins/".$pid."/assets");
    endif;
    $pconfig=APP_ROOT."/plugins/".$pid."/usualtool.config";
    $plugins=file_get_contents($pconfig);
    $type=Inc::StrSubstr("<type>","</type>",$plugins);
    $auther=Inc::StrSubstr("<auther>","</auther>",$plugins);
    $title=Inc::StrSubstr("<title>","</title>",$plugins);
    $ver=Inc::StrSubstr("<ver>","</ver>",$plugins);
    $description=Inc::StrSubstr("<description>","</description>",$plugins);
    $installsql=Inc::StrSubstr("<installsql><![CDATA[","]]></installsql>",$plugins);
    if(Data::QueryData("cms_plugin","","pid='$pid'","","1")["querynum"]>0):
        Data::UpdateData("cms_plugin",array(
            "type"=>$type,
            "auther"=>$auther,
            "title"=>$title,
            "ver"=>$ver,
            "description"=>$description),"pid='$pid'");
    else:
        Data::InsertData("cms_plugin",array(
            "pid"=>$pid,
            "type"=>$type,
            "auther"=>$auther,
            "title"=>$title,
            "ver"=>$ver,
            "description"=>$description));
    endif;
    if($installsql=='0'):
        Inc::GoUrl(Route::Link("forum","my_admin_plugin","t=".$d),"成功安装插件!");
    else:
        if(Data::RunSql($installsql)):
            Inc::GoUrl(Route::Link("forum","my_admin_plugin","t=".$d),"成功安装插件!");
        else:
            Inc::GoUrl("-1","插件安装失败!");
        endif;   
    endif;
}
if($do=="uninstall"){
    $d=$_GET["d"];
    $pid=str_replace(".","",Inc::SqlCheck($_GET["pid"]));
    $pconfig=APP_ROOT."/plugins/".$pid."/usualtool.config";
    $plugins=file_get_contents($pconfig);
    $uninstallsql=Inc::StrSubstr("<uninstallsql><![CDATA[","]]></uninstallsql>",$plugins);
    Data::DelData("cms_plugin","pid='$pid'");
    if($uninstallsql=='0'):
        Inc::DelDir(APP_ROOT."/plugins/".$pid);
		    if(is_dir(OPEN_ROOT."/assets/plugins/".$pid)):
            Inc::DelDir(OPEN_ROOT."/assets/plugins/".$pid);
				endif;
        Inc::GoUrl(Route::Link("forum","my_admin_plugin","t=".$d),"成功卸载插件!");
    else:
        if(Data::RunSql($uninstallsql)):
            Inc::DelDir(APP_ROOT."/plugins/".$pid);
		        if(is_dir(OPEN_ROOT."/assets/plugins/".$pid)):
                Inc::DelDir(OPEN_ROOT."/assets/plugins/".$pid);
				    endif;
            Inc::GoUrl(Route::Link("forum","my_admin_plugin","t=".$d),"成功卸载插件!");
        else:
            Inc::GoUrl("-1","插件卸载失败!");
        endif;   
    endif;
}