<?php
namespace usualtool\Lib;
use usualtool\Lib\Inc;
use usualtool\Lib\Temp;
/**
       * --------------------------------------------------------       
       *  |                  █   █ ▀▀█▀▀                    |           
       *  |                  █▄▄▄█   █                      |           
       *  |                                                 |           
       *  |    Author: Huang Hui                            |           
       *  |    Repository 1: https://gitee.com/usualtool    |           
       *  |    Repository 2: https://github.com/usualtool   |           
       *  |    Applicable to Apache 2.0 protocol.           |           
       * --------------------------------------------------------       
*/
/**
 * 缓存/编译
 */
class Cache{
    private static $dirsWritten=[];
    private static $failAll=[];
    /**
     * 选项解析
     * @param array $args 形如 ['--dry','--mod=guestbook']
     * @return array
     */
    public static function Parse($args){
        $opt=['mod'=>null,'dry'=>false,'bad'=>[]];
        foreach((array)$args as $a){
            if($a==='--dry'){ $opt['dry']=true; }
            elseif(strpos($a,'--mod=')===0){ $opt['mod']=substr($a,6); }
            else{ $opt['bad'][]=$a; }
        }
        return $opt;
    }
    /**
     * 解析参数并拦截未知选项
     * @param array $args
     * @return array|null
     */
    private static function Args($args){
        $opt=self::Parse($args);
        if(!empty($opt['bad'])){
            self::Out('未知参数：'.implode(' ',$opt['bad']));
            self::Help();
            return null;
        }
        return $opt;
    }
    /**
     * 全量重建模板缓存
     * @param array $args 命令行参数，如 ['--dry','--mod=guestbook']
     * @return int
     */
    public static function Rebuild($args=[]){
        $opt=self::Args($args);
        if($opt===null){ return 1; }
        self::$dirsWritten=[];
        self::$failAll=[];
        $config=self::Boot();
        $forms=['front','admin'];
        $mods=self::Modules($opt['mod']);
        $sum=['tpl'=>0,'built'=>0,'skip'=>0,'fail'=>0,'warn'=>0];
        self::Out('UT 模板缓存重建'.($opt['dry']?'（预演，不写文件）':''));
        self::Out('框架根目录：'.UTF_ROOT);
        self::Out("TEMPCACHE 当前值：{$config['TEMPCACHE']}（=1 页面每请求自己重编译；其它值只读缓存，需本命令刷新）");
        self::Out('');
        foreach($mods as $m){
            if(!is_dir(APP_ROOT.'/modules/'.$m)){ self::Out("[跳过] 模块 {$m} 不存在"); continue; }
            foreach($forms as $form){
                list($tempdir,$cachedir)=self::Paths($m,$form,$config);
                if(!is_dir($tempdir)){ continue; }
                $tpls=glob($tempdir.'/*.cms')?:[];
                if(!$tpls){ continue; }
                sort($tpls);
                $app=new Temp(0,$tempdir,$cachedir);
                $app->Runin(
                    ['appname','appurl','module','page','lang','thelang','pubtemp','template'],
                    [$config['APPNAME'],$config['APPURL'],$m,$config['DEFAULT_PAGE'],
                     explode(',',$config['LANG_OPTION']),$config['LANG'],
                     PUB_TEMP.'/'.$form,
                     APP_ROOT.'/template/'.($form==='admin'?$config['TEMPADMIN']:$config['TEMPFRONT'])
                        .'/skin/'.$config['DEFAULT_MOD'].'/'.$form]
                );
                $stat=['built'=>0,'skip'=>0,'fail'=>0];
                $warns=[];
                foreach($tpls as $tpl){
                    $name=basename($tpl);
                    $src=file_get_contents($tpl);
                    $sum['tpl']++;
                    try{
                        $out=$app->TempReplace($src);
                    }catch(\Throwable $e){
                        $stat['fail']++; $sum['fail']++;
                        self::$failAll[]="{$m}/{$form}/{$name}: ".$e->getMessage();
                        continue;
                    }
                    if(preg_match('/<\{[^(\}>)]{1,}\}>/',$out,$mm)){
                        $warns[]="{$name} 残留未编译标签 ".$mm[0];
                    }
                    $cacheFile=$cachedir.'/cache_'.$name;
                    $old=is_file($cacheFile)?file_get_contents($cacheFile):null;
                    if($old===$out){ $stat['skip']++; $sum['skip']++; continue; }
                    if($opt['dry']){
                        $stat['built']++; $sum['built']++;
                        self::$dirsWritten[$cachedir]=true;
                        continue;
                    }
                    Inc::MakeDir($cachedir);
                    if(file_put_contents($cacheFile,$out)===false){
                        $stat['fail']++; $sum['fail']++;
                        self::$failAll[]="{$m}/{$form}/{$name}: 写入失败（权限？）";
                        continue;
                    }
                    $stat['built']++; $sum['built']++;
                    self::$dirsWritten[$cachedir]=true;
                }
                self::Out(sprintf('· %-14s %-5s 模板 %2d → 重建 %2d，无变化 %2d，失败 %d',
                    $m,$form,count($tpls),$stat['built'],$stat['skip'],$stat['fail']));
                foreach($warns as $w){ self::Out('    警告：'.$w); $sum['warn']++; }
            }
        }
        self::Out('');
        self::Out("合计：模板 {$sum['tpl']}，重建 {$sum['built']}，无变化 {$sum['skip']}，失败 {$sum['fail']}，警告 {$sum['warn']}");
        foreach(self::$failAll as $f){ self::Out('  失败：'.$f); }
        if(self::$dirsWritten && PHP_OS_FAMILY!=='Windows'){
            self::Out('提示：以 root 执行会把缓存属主改为 root，若 Web 以 www 用户运行请执行：');
            self::Out('  chown -R www:www '.implode(' ',array_keys(self::$dirsWritten)));
        }
        return $sum['fail']>0?1:0;
    }
    public static function Help(){
        self::Out('php usualtool cache rebuild 重建整站缓存');
        self::Out('php usualtool cache rebuild [--mod=xxx] 重建指定模块缓存');
        self::Out('php usualtool cache rebuild [--dry] 预演只编译');
        self::Out('php usualtool cache help 缓存命令帮助');
    }
    /**
     * 载入框架配置并确保公共常量就绪
     * @return array
     */
    private static function Boot(){
        if(!defined('UTF_ROOT') || !is_file(UTF_ROOT.'/bootstrap.php')){
            $root=getcwd();
            while($root!==dirname($root) && !is_file($root.'/bootstrap.php')){ $root=dirname($root); }
            if(!is_file($root.'/bootstrap.php')){
                self::Out('未找到框架根目录（bootstrap.php），请把本命令放在站点根目录执行。');
                exit(1);
            }
            defined('UTF_ROOT')  or define('UTF_ROOT',$root);
            defined('APP_ROOT')  or define('APP_ROOT',$root.'/app');
            defined('OPEN_ROOT') or define('OPEN_ROOT',$root.'/open');
            require_once UTF_ROOT.'/autoload.php';
        }
        $config=Inc::GetConfig();
        defined('PUB_PATH') or define('PUB_PATH',APP_ROOT.'/modules/'.$config['DEFAULT_MOD']);
        defined('PUB_TEMP') or define('PUB_TEMP',PUB_PATH.'/skin');
        return $config;
    }
    /**
     * 解析某模块某端的模板目录与缓存目录
     * @return array
     */
    private static function Paths($m,$form,$config){
        $deve=($form==='admin');
        $work=APP_ROOT.'/template/'.($deve?$config['TEMPADMIN']:$config['TEMPFRONT']);
        $node=(($deve && $config['TEMPADMIN']!=='0') || (!$deve && $config['TEMPFRONT']!=='0'))
            ?$work:APP_ROOT.'/modules/'.$m;
        $skin=$node.($node===$work?'/skin/'.$m:'/skin');
        $temp=$skin.'/'.$form;
        $cache=$node.'/cache/'.$form;
        return [$temp,$cache];
    }
    /**
     * 待处理的模块清单
     * @return array
     */
    private static function Modules($mod){
        if(!empty($mod)){ return [$mod]; }
        $mods=[];
        $dir=APP_ROOT.'/modules';
        if(!is_dir($dir)){ return $mods; }
        foreach(scandir($dir) as $d){
            if($d!=='.' && $d!=='..' && is_dir($dir.'/'.$d)){ $mods[]=$d; }
        }
        sort($mods);
        return $mods;
    }
    private static function Out($msg=''){
        echo $msg."\n";
    }
}