<?php
namespace Model\Forum;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
class Plugin{
    public function GetList($t){
        $plugin=array();
        $hooks=Inc::Auth("plugin");
        preg_match_all("/\<hook\>(.*?)\<\/hook\>/s",$hooks,$hookblocks);
        if(!empty($hookblocks[1])):
            foreach($hookblocks[1] as $hook){
                $plugin[]=array(
                    "pid"=>self::HookVal("pid",$hook),
                    "type"=>self::HookVal("type",$hook),
                    "title"=>self::HookVal("title",$hook),
                    "picurl"=>self::HookVal("picurl",$hook),
                    "ver"=>self::HookVal("ver",$hook),
                    "isfree"=>self::HookVal("isfree",$hook)
                );
            }
        endif;
        $data=$plugin;
        if(empty($t)):
            $plugin = array_filter($data, function($item) {
                $pid = (string)($item['pid'] ?? '');
                return substr($pid, 0, 2) === 'f_';
            });
        else:
            $plugin = array_filter($data, function($item) {
                $pid = (string)($item['pid'] ?? '');
                return substr($pid, 0, 2) !== 'f_';
            });
        endif;
        return array_values($plugin);
    }
    private static function HookVal($tag,$hook){
        return preg_match("/<".$tag.">(.*?)<\/".$tag.">/s",$hook,$match) ? $match[1] : "";
    }
    public function GetDetail($pid){
        return Data::QueryData("cms_plugin","","pid='$pid'");
    }
    public function Install($pid){
        global $config;
        $down=Inc::Auth($config["UTCODE"],$config["UTFURL"],"plugin-".$pid);
        $downurl=Inc::StrSubstr("<downurl>","</downurl>",$down);
        $filename=basename($downurl);
        $res=Inc::SaveFile($downurl,APP_ROOT."/plugins",$filename,1);
        if(empty($res)){
            return "denied";
        }
        Inc::Auth($config["UTCODE"],$config["UTFURL"],"plugindel-".str_replace(".zip","",$filename)."");
        $zip=new \ZipArchive;
        if($zip->open(APP_ROOT."/plugins/".$filename)===TRUE):
            $zip->extractTo(APP_ROOT."/plugins/");
            $zip->close();
            unlink(APP_ROOT."/plugins/".$filename);
        else:
            return "zip";
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
        $cate=Inc::StrSubstr("<cate>","</cate>",$plugins);
        $auther=Inc::StrSubstr("<auther>","</auther>",$plugins);
        $title=Inc::StrSubstr("<title>","</title>",$plugins);
        $ver=Inc::StrSubstr("<ver>","</ver>",$plugins);
        $description=Inc::StrSubstr("<description>","</description>",$plugins);
        $installsql=Inc::StrSubstr("<installsql><![CDATA[","]]></installsql>",$plugins);
        if(Data::QueryData("cms_plugin","","pid='$pid'","","1")["querynum"]>0):
            Data::UpdateData("cms_plugin",array(
                "type"=>$cate,
                "auther"=>$auther,
                "title"=>$title,
                "ver"=>$ver,
                "description"=>$description),"pid='$pid'");
        else:
            Data::InsertData("cms_plugin",array(
                "pid"=>$pid,
                "type"=>$cate,
                "auther"=>$auther,
                "title"=>$title,
                "ver"=>$ver,
                "description"=>$description));
        endif;
        if($installsql=='0'):
            return "ok";
        endif;
        if(Data::RunSql($installsql)):
            return "ok";
        endif;
        return "sql";
    }
    public function Uninstall($pid){
        $pconfig=APP_ROOT."/plugins/".$pid."/usualtool.config";
        $plugins=file_get_contents($pconfig);
        $uninstallsql=Inc::StrSubstr("<uninstallsql><![CDATA[","]]></uninstallsql>",$plugins);
        Data::DelData("cms_plugin","pid='$pid'");
        if($uninstallsql=='0'):
            Inc::DelDir(APP_ROOT."/plugins/".$pid);
            if(is_dir(OPEN_ROOT."/assets/plugins/".$pid)):
                Inc::DelDir(OPEN_ROOT."/assets/plugins/".$pid);
            endif;
            return "ok";
        endif;
        if(Data::RunSql($uninstallsql)):
            Inc::DelDir(APP_ROOT."/plugins/".$pid);
            if(is_dir(OPEN_ROOT."/assets/plugins/".$pid)):
                Inc::DelDir(OPEN_ROOT."/assets/plugins/".$pid);
            endif;
            return "ok";
        endif;
        return "sql";
    }
    public function Sync($pid){
        $plugin_dir=APP_ROOT."/plugins/".$pid;
        $plugin_file=$plugin_dir."/admin.php";
        $config_file=$plugin_dir."/usualtool.config";
        $code=is_file($config_file) ? Inc::StrSubstr("<code><![CDATA[","]]></code>",file_get_contents($config_file)) : "";
        if(!empty($code) && $code!="0"){
            $code=preg_replace(array('/^\s*\?>/','/^\r?\n/'),'',$code);
            $code=str_replace(Route::Link("ut-plugin", "plugin_view"),Route::Link("forum", "my_admin_plugin_view"),$code);
            $sign=md5($code);
            $text=is_file($plugin_file) ? file_get_contents($plugin_file) : "";
            $marked=preg_match('/\/\*'.$pid.'-md5:([0-9a-f]{32})\*\//',$text,$m) ? $m[1] : "";
            if($text==="" || ($marked!=="" && $marked!==$sign)){
                @file_put_contents($plugin_file,"<?php /*".$pid."-md5:".$sign."*/?>\r\n".$code);
            }
        }elseif(!is_file($plugin_file)){
            $plugin_file="";
        }
        return $plugin_file;
    }
}