<?php
namespace usualtool\Lib;
use usualtool\Lib\Inc;
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
 * 操作路由
 */
class Route{
    /**
     * URL易错特殊参数转义
     * @param string $url
     * @return string
     */
    public static function ConverUrl($url){
        $o=array(
            "&times","&cent","&pound","&yen","&shy",
            "&thorn","&sect","&micro","&uml","&reg",
            "&laquo","&para","&acute","&deg","&eth",
            "&raquo","&copy","&aquo","&not","&curren"
        );
        $n=array(
            "&amp;times","&amp;cent","&amp;pound","&amp;yen","&amp;shy",
            "&amp;thorn","&amp;sect","&amp;micro","&amp;uml","&amp;reg",
            "&amp;laquo","&amp;para","&amp;acute","&amp;deg","&amp;eth",
            "&amp;raquo","&amp;copy","&amp;aquo","&amp;not","&amp;curren"
        );
        $url=str_replace($o,$n,$url);
        return $url;
    }
    /**
     * 解析路由
     * @param string $url
     * @return array
     */
    public static function Analy($url=''){
        $config = Inc::GetConfig();
        $param = array();
        $path = self::Path($url);
        $develop = trim($config["DEVELOP"] ?? '', '/');
        $control_type = self::Control($path);
        $param['_control_'] = $control_type;
        $segments = array_values(array_filter(explode('/', $path)));
        if ($control_type === "admin" && !empty($develop) && isset($segments[0]) && $segments[0] === $develop) {
            array_shift($segments);
        }
        $m = $config["DEFAULT_MOD"] ?? 'index';
        $p = $config["DEFAULT_PAGE"] ?? 'index';
        $hasm = false;
        $hasp = false;
        if (count($segments) >= 1) {
            $m = $segments[0];
            $hasm = true;
            if (isset($segments[1])) {
                $p = $segments[1];
                $hasp = true;
            }
        }
        $param['m'] = $m;
        $param['p'] = $p;
        if(!empty($_SERVER['QUERY_STRING'])){
            $cleanQuery=str_replace('&amp;','&',$_SERVER['QUERY_STRING']); 
            parse_str($cleanQuery,$queryParams);
            $param=array_merge($param,$queryParams);
            if($hasm){$param['m']=$m;}
            if($hasp){$param['p']=$p;}
        }
        if($control_type === "admin"){ $param['_control_'] = "admin"; }
        $m = self::Verify($param['m'] ?? '');
        $param['m'] = self::Module($m === '' ? ($config["DEFAULT_MOD"] ?? 'index') : $m);
        $p = self::Verify($param['p'] ?? '');
        $param['p'] = ($p === '' ? ($config["DEFAULT_PAGE"] ?? 'index') : $p);
        return $param;
    }
    public static function Path($url=''){
        $config = Inc::GetConfig();
        if(empty($url)){
            $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
            $path = parse_url($requestUri, PHP_URL_PATH);
            $scriptName = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
            $path = str_replace("/".$scriptName,"",$path);
        }else{
            $url = self::ConverUrl($url);
            $parsedUrl = parse_url($url);
            $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : $url;
        }
        if(!empty($config["APPURL"])){
            $path = preg_replace('#^'.preg_quote($config["APPURL"],'#').'#','',$path);
        }
        return trim($path,'/');
    }
    public static function Control($url=''){
        $config = Inc::GetConfig();
        $develop = trim($config["DEVELOP"] ?? '', '/');
        if(empty($develop)){
            return "front";
        }
        $path = self::Path($url);
        return ($path === $develop || strpos($path, $develop."/") === 0) ? "admin" : "front";
    }
    public static function Verify($name){
        if(!is_string($name)){
            return "";
        }
        $name = str_replace(".php","",$name);
        return preg_match('/^[A-Za-z0-9_-]+$/', $name) ? $name : "";
    }
    public static function Map($file){
        if (file_exists($file)) {
            $map = include $file;
            if (is_array($map)) {
                return $map;
            }
        }
        return array();
    }
    public static function Module($m){
        $map = self::Map(OPEN_ROOT.'/route.php');
        if (is_string($m) && isset($map[$m])) {
            return $map[$m];
        }
        return $m;
    }
    public static function Page($modpath, $form, $page){
        $map = self::Map($modpath."/route.php");
        $page = self::Verify($page);
        if (isset($map[$page])) {
            $page = $map[$page];
        }
        return $modpath."/".$form."/".$page.".php";
    }
    public static function Alias($m){
        $map = self::Map(OPEN_ROOT.'/route.php');
        $key = is_string($m) ? array_search($m,$map,true) : false;
        return ($key === false) ? $m : $key;
    }
    /**
     * 编制路由
     * Link("article","index","cid=1&page=1")
     * @param string $module
     * @param string $page
     * @param string $param
     * @return string
     */
    public static function Link($module="", $page="", $param=""){
        $config = Inc::GetConfig();
        $m = empty($module) ? $config["DEFAULT_MOD"] : $module;
        $m = self::Alias($m);
        $p = empty($page) ? $config["DEFAULT_PAGE"] : str_replace(".php","",$page);
        $dev = (strpos($_SERVER["REQUEST_URI"],$config["DEVELOP"])===0);
        $place = $dev ? $config["DEVELOP"] : "";
        if($config["REWRITE"]==0){
            $params = ['m'=>$m,'p'=>$p];
            if(!empty($param)){
                $extraParams = self::UrlToArray($param);
                if(is_array($extraParams)){
                    $params=array_merge($params, $extraParams);
                }
            }
            $link=$place."?".http_build_query($params);
        }elseif($config["REWRITE"]==1){
            $link=$place."/{$m}/{$p}";
            if(!empty($param)){
                $link.="?".$param;
            }
        }
        return $link;
    }
    /**
     * 解析URL
     * @param string $url
     * @return array
     */
    public static function UrlToArray($url){
        $params = array();
        $query = explode('&', $url);
        foreach ($query as $param) {
            $item = explode('=', $param);
            if(isset($item[0]) && isset($item[1])){
                $params[$item[0]] = $item[1];
            } elseif(isset($item[0])) {
                $params[$item[0]] = '';
            }
        }
        return $params;
    }
    /**
     * 获取指定URL参数值
     * @param string $url
     * @param string $key
     * @return string
     */
     public static function GetUrlVal($url, $key){
        $res='';
        $a=strpos($url, '?');
        if($a!==false){
            $str=substr($url, $a + 1);
        }else{
            $str = $url;
        }
        $arr=explode('&', $str);
        foreach($arr as $v){
            $tmp = explode('=', $v);
            if(isset($tmp[0]) && isset($tmp[1]) && $tmp[0] == $key){
                $res = $tmp[1];
                break;
            }
        }
        return $res;
    }
}