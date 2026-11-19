<?php
namespace usualtool\Lib;
use usualtool\Lib\App;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use usualtool\Lib\Temp;
use usualtool\Lib\Power;
use usualtool\Lib\Debug;
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
 * 框架请求内核
 */
class Kernel{
    /**
     * 是否运行在常驻进程
     * @var bool
     */
    private static $longrun=false;
    /**
     * 待发送的响应头
     * @var array
     */
    private static $headers=array();
    /**
     * 响应状态码
     * @var int
     */
    private static $status=200;
    /**
     * 主动终止时携带的响应体
     * @var string|null
     */
    private static $body=null;
    /**
     * 当前请求上下文
     * @var array
     */
    private static $vars=array();
    /**
     * 启动：配置、路由、模板、容器
     * @return array 上下文变量
     */
    public static function Bootstrap(){
        ini_set('display_errors','Off');
        ini_set('log_errors','On');
        ini_set('error_log',UTF_ROOT.'/log/php_errors.log');
        ini_set('session.cookie_httponly','1');
        ini_set('session.cookie_samesite','Lax');
        if((string)Inc::Config('SESSION_SECURE')==='1'){
            ini_set('session.cookie_secure','1');
        }
        ini_set('session.use_strict_mode','1');
        error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
        /**
         * 会话
         */
        if(session_status()===PHP_SESSION_NONE){
            $name=session_name();
            if(self::$longrun){
                $sid=isset($_COOKIE[$name]) ? preg_replace('/[^a-zA-Z0-9,-]/','',$_COOKIE[$name]) : '';
                session_id($sid!=='' ? $sid : '');
            }
            @session_start();
            if(self::$longrun && session_status()===PHP_SESSION_ACTIVE){
                $_COOKIE[$name]=session_id();
            }
        }
        /**
         * 读取配置
         */
        $config=Inc::GetConfig();
        /**
         * 公共模块/模板配置
         */
        defined('PUB_PATH') or define('PUB_PATH',APP_ROOT.'/modules/'.$config["DEFAULT_MOD"]);
        defined('PUB_TEMP') or define('PUB_TEMP',PUB_PATH.'/skin');
        /**
         * 业务级全局路径
         */
        defined('MODULE_PATH') or define('MODULE_PATH',APP_ROOT.'/modules');
        defined('TEMPLATE_PATH') or define('TEMPLATE_PATH',APP_ROOT.'/template');
        defined('PLUGIN_PATH') or define('PLUGIN_PATH',APP_ROOT.'/plugins');
        defined('TASK_PATH') or define('TASK_PATH',APP_ROOT.'/task');
        defined('LANG_PATH') or define('LANG_PATH',OPEN_ROOT.'/lang');
        /**
         * 插件入口只需配置
         */
        if(pathinfo($_SERVER['SCRIPT_NAME'] ?? '',PATHINFO_FILENAME)==='plugin'){
            self::$vars=array("plugin"=>true,"config"=>$config);
            return self::$vars;
        }
        /**
         * 识别入口/路由
         */
        foreach(Route::Analy(Inc::CurPageUrl()) as $k=>$v){
            $_GET[$k]=$v;
        }
        /**
         * 模块/控制/依赖
         */
        $m=Inc::SqlCheck($_GET["m"] ?? $config["DEFAULT_MOD"]);
        $p=Inc::SqlCheck(str_replace(".php","",$_GET["p"] ?? $config["DEFAULT_PAGE"]));
        $_modpath_=APP_ROOT."/modules/".$m;
        Inc::HasComposer($_modpath_);
        /**
         * 入口节点/模板预设
         */
        $_deve_=(($_GET["_control_"] ?? "")=="admin");
        $_form_=$_deve_ ? "admin" : "front";
        $_work_=APP_ROOT."/template/".($_deve_ ? $config["TEMPADMIN"] : $config["TEMPFRONT"]);
        $_node_=(($_deve_ && $config["TEMPADMIN"]!="0") || (!$_deve_ && $config["TEMPFRONT"]!="0")) ? $_work_ : $_modpath_;
        $_skin_=$_node_.($_node_===$_work_ ? "/skin/".$m : "/skin");
        $_cache_=($_node_===$_work_ ? $_skin_ : $_node_)."/cache";
        /**
         * 本地化语言
         */
        $_lang_=!empty($_COOKIE["lang"]) ? Inc::SqlCheck($_COOKIE["lang"]) : $config["LANG"];
        if(!isset($_COOKIE["lang"])){
            self::Cookie("lang",$_lang_);
            $_COOKIE["lang"]=$_lang_;
        }
        /**
         * 启动引擎/全局预设
         */
        $app=new Temp($config["TEMPCACHE"],$_skin_."/".$_form_,$_cache_."/".$_form_);
        $app->Runin(
            array(
                "appname",//应用名称
                "appurl",//应用地址
                "module",//当前模块
                "page",//当前页/方法
                "lang",//本地化语言列表
                "thelang",//当前语言
                "pubtemp",//模块化公共模板路径
                "template"//工程化模板路径
            ),array(
                $config["APPNAME"],
                $config["APPURL"],
                $m,
                $p,
                explode(",",$config["LANG_OPTION"]),
                $config["LANG"],
                PUB_TEMP."/".$_form_,
                $_work_."/skin/".$config["DEFAULT_MOD"]."/".$_form_
            )
        );
        /**
         * 登记引擎/绑定服务
         */
        App::Boot($app);
        self::$vars=array(
            "config"=>$config,
            "m"=>$m,
            "p"=>$p,
            "_modpath_"=>$_modpath_,
            "_deve_"=>$_deve_,
            "_form_"=>$_form_,
            "_work_"=>$_work_,
            "_node_"=>$_node_,
            "_skin_"=>$_skin_,
            "_cache_"=>$_cache_,
            "_lang_"=>$_lang_,
            "app"=>$app,
            "plugin"=>false
        );
        foreach(self::$vars as $key=>$value){
            $GLOBALS[$key]=$value;
        }
        return self::$vars;
    }
    /**
     * 分发：路由页面、权限、控制器
     * @return void
     */
    public static function Dispatch(){
        $vars=self::$vars;
        if(empty($vars) || !empty($vars["plugin"])){
            return;
        }
        extract($vars,EXTR_OVERWRITE);
        /**
         * 路由分发控制
         */
        $file=Route::Page($_modpath_,$_form_,$p);
        /**
         * 全局权限中间件
         */
        $gate=new Power($_deve_,MODULE_PATH);
        $gate->SetPage($m."/".$_form_."/".basename($file,".php"))->Check();
        /**
         * 判断文件真实性
         */
        if(Inc::SearchFile($file)){
            $class=Inc::GetClassName($file);
            if($class!==null){
                /**
                 * 分层模式
                 */
                require_once $file;
                $action=$_GET["action"] ?? "";
                $action=is_string($action) && preg_match('/^[a-zA-Z0-9_]+$/',$action) ? $action : "index";
                try{
                    $controller=App::Make($class);
                }catch(\Throwable $e){
                    $controller=new $class();
                }
                if(is_callable(array($controller,$action))){
                    $controller->$action();
                }else{
                    Debug::Error("action",$class."/".$action);
                }
            }else{
                /**
                 * 过程式页面：每请求执行
                 */
                extract(array_diff_key($GLOBALS,array_flip(array('GLOBALS','_SERVER','_GET','_POST','_COOKIE','_SESSION','_FILES','_ENV','_REQUEST'))),EXTR_SKIP);
                require $file;
            }
        }else{
            Debug::Error("module",str_replace(APP_ROOT."/modules","",$file));
        }
        $config["DEBUG"] && Debug::Debug($config["DEBUG_BAR"]);
    }
    /**
     * 常驻进程完整请求：环境注入 + 启动 + 分发 + 输出缓冲
     * @param array $options server/get/post/cookie
     * @return array output/headers/status/vars
     */
    public static function Handle($options=array()){
        self::$longrun=true;
        self::$headers=array();
        self::$status=200;
        self::$body=null;
        self::$vars=array();
        Temp::$repeat=true;
        error_clear_last();
        /**
         * 注入超级全局
         */
        $server=(isset($options['server']) && is_array($options['server'])) ? $options['server'] : $_SERVER;
        $get=(isset($options['get']) && is_array($options['get'])) ? $options['get'] : $_GET;
        $cookie=(isset($options['cookie']) && is_array($options['cookie'])) ? $options['cookie'] : $_COOKIE;
        $_SERVER=$server;
        $_GET=$get;
        $_COOKIE=$cookie;
        $sessName=session_name();
        $_sid_in_=isset($_COOKIE[$sessName]) ? preg_replace('/[^a-zA-Z0-9,-]/','',$_COOKIE[$sessName]) : '';
        $_POST=isset($options['post']) && is_array($options['post']) ? $options['post'] : array();
        $_FILES=isset($options['files']) && is_array($options['files']) ? $options['files'] : array();
        if(session_status()!==PHP_SESSION_ACTIVE){
            $_SESSION=array();
        }
        $_SERVER['REQUEST_URI']=$_SERVER['REQUEST_URI'] ?? '/';
        $_SERVER['SCRIPT_NAME']=$_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $_SERVER['HTTP_HOST']=$_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
        $_SERVER['QUERY_STRING']=$_SERVER['QUERY_STRING'] ?? '';
        $_REQUEST=array_merge($_GET,$_POST);
        foreach(array_diff(array_keys($GLOBALS),array('GLOBALS','_SERVER','_GET','_POST','_COOKIE','_SESSION','_FILES','_ENV','_REQUEST','argv','argc')) as $_key_){
            unset($GLOBALS[$_key_]);
        }
        /**
         * 执行框架入口（启动 + 分发）
         */
        $level=ob_get_level();
        ob_start();
        $output='';
        try{
            require OPEN_ROOT.'/config.php';
            self::Dispatch();
        }catch(Request $e){
            /**
             * 主动终止：应用状态码/响应头/响应体
             */
            self::Terminate($e);
        }catch(\Throwable $e){
            while(ob_get_level()>$level){
                ob_end_clean();
            }
            if(session_status()===PHP_SESSION_ACTIVE){
                if(session_id()!==$_sid_in_){
                    self::$headers[]="Set-Cookie: ".$sessName."=".session_id()."; Path=/; HttpOnly; SameSite=Lax".((($_SERVER['HTTPS'] ?? ''))==='on' ? "; Secure" : "");
                }
                session_write_close();
            }
            throw $e;
        }
        /**
         * 收集剩余输出缓冲（含嵌套）
         */
        while(ob_get_level()>$level){
            $output=ob_get_clean().$output;
        }
        if(self::$body!==null){
            $output=self::$body;
        }
        if(session_status()===PHP_SESSION_ACTIVE){
            if(session_id()!==$_sid_in_){
                self::$headers[]="Set-Cookie: ".$sessName."=".session_id()."; Path=/; HttpOnly; SameSite=Lax".((($_SERVER['HTTPS'] ?? ''))==='on' ? "; Secure" : "");
            }
            session_write_close();
        }
        return array(
            "output"=>$output,
            "headers"=>self::$headers,
            "status"=>self::$status,
            "vars"=>array(
                "config"=>self::$vars["config"] ?? array(),
                "m"=>self::$vars["m"] ?? '',
                "p"=>self::$vars["p"] ?? '',
                "_skin_"=>self::$vars["_skin_"] ?? ''
            )
        );
    }
    /**
     * 是否常驻进程模式
     * @return bool
     */
    public static function IsLongRun(){
        return self::$longrun;
    }
    /**
     * 设置 Cookie
     * @param string $name
     * @param string $value
     * @return void
     */
    public static function Cookie($name,$value){
        if(self::$longrun){
            self::$headers[]="Set-Cookie: ".$name."=".rawurlencode($value)."; Path=/; SameSite=Lax";
            return;
        }
        setcookie($name,$value);
    }
    /**
     * 追加响应头
     * @param string $header
     * @return void
     */
    public static function Header($header){
        if(self::$longrun){
            self::$headers[]=$header;
            return;
        }
        if(!headers_sent()){
            header($header);
        }
    }
    /**
     * 设置响应状态码
     * @param int $code
     * @return void
     */
    public static function Status($code){
        self::$status=$code;
        if(!self::$longrun && !headers_sent()){
            http_response_code($code);
        }
    }
    /**
     * 应用主动终止信号
     * @param Request $e
     * @return void
     */
    public static function Terminate(Request $e){
        $status=$e->getStatus();
        if($status>0){
            self::Status($status);
        }
        foreach($e->getHeaders() as $name=>$value){
            self::Header($name.": ".$value);
        }
        $body=$e->getBody();
        if($body===null){
            return;
        }
        if(self::$longrun){
            self::$body=$body;
            return;
        }
        echo $body;
    }
}