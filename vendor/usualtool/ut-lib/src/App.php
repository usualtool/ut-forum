<?php
namespace usualtool\Lib;
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
 * 应用容器
 */
class App{
    /* 引擎 */
    private static $engine=null;
    /* 容器 */
    private static $bindings=array();
    private static $instances=array();
    private static $resolving=array();
    /**
     * 引导
     * @param object $engine Temp 引擎
     * @return void
     */
    public static function Boot($engine){
        self::Set($engine);
        defined('APP_ROOT') or define('APP_ROOT',UTF_ROOT.'/app');
        foreach(glob(APP_ROOT."/modules/*/bind.php") ?: array() as $bind_file){
            self::LoadBindings(include $bind_file);
        }
    }
    /**
     * 配置引擎实例
     * @param object $engine Temp 引擎
     */
    public static function Set($engine){
        self::$engine=$engine;
        self::$instances['usualtool\Lib\Temp']=$engine;
    }
    /**
     * 读取引擎实例
     * @return object|null
     */
    public static function Engine(){
        return self::$engine;
    }
    /**
     * 绑定抽象到实现
     * @param string $abstract 抽象名
     * @param string|null $concrete 实现类名，为空表示绑定自身
     */
    public static function Bind($abstract,$concrete=null){
        self::$bindings[$abstract]=array($concrete===null ? $abstract : $concrete,false);
    }
    /**
     * 绑定单例
     * @param string $abstract
     * @param string|null $concrete
     */
    public static function Singleton($abstract,$concrete=null){
        self::$bindings[$abstract]=array($concrete===null ? $abstract : $concrete,true);
    }
    /**
     * 直接登记一个已创建的实例
     * @param string $abstract
     * @param object $instance
     * @return object
     */
    public static function Instance($abstract,$instance){
        self::$instances[$abstract]=$instance;
        return $instance;
    }
    /**
     * 是否已绑定
     */
    public static function Has($abstract){
        return isset(self::$bindings[$abstract]) || isset(self::$instances[$abstract]);
    }
    /**
     * 移除绑定与实例（测试）
     */
    public static function Forget($abstract){
        unset(self::$bindings[$abstract],self::$instances[$abstract]);
    }
    /**
     * 批量绑定
     * @param array $bindings 抽象名=>实现
     */
    public static function LoadBindings($bindings){
        if(!is_array($bindings)){
            return;
        }
        foreach($bindings as $abstract=>$concrete){
            if($concrete instanceof \Closure){
                self::Bind($abstract,$concrete);
            }elseif(is_array($concrete) && isset($concrete[1]) && $concrete[1]===true){
                self::Singleton($abstract,$concrete[0]);
            }else{
                self::Bind($abstract,$concrete);
            }
        }
    }
    /**
     * 从容器取实例
     * @param string $abstract 抽象名或类名
     * @return object
     * @throws \Exception
     */
    public static function Make($abstract){
        if(isset(self::$instances[$abstract])){
            return self::$instances[$abstract];
        }
        $single=false;
        $concrete=$abstract;
        if(isset(self::$bindings[$abstract])){
            $concrete=self::$bindings[$abstract][0];
            $single=self::$bindings[$abstract][1];
        }
        if($concrete instanceof \Closure){
            $object=$concrete();
            if($single){ self::$instances[$abstract]=$object; }
            return $object;
        }
        if(!class_exists($concrete)){
            throw new \Exception("App::Make 无法解析：".$concrete."（类不存在或未绑定）");
        }
        if(isset(self::$resolving[$concrete])){
            throw new \Exception("App::Make 检测到循环依赖：".$concrete);
        }
        self::$resolving[$concrete]=true;
        try{
            $ref=new \ReflectionClass($concrete);
            $ctor=$ref->getConstructor();
            $args=array();
            if($ctor!==null){
                foreach($ctor->getParameters() as $param){
                    $args[]=self::ResolveParam($param);
                }
            }
            $object=$ref->newInstanceArgs($args);
        }finally{
            unset(self::$resolving[$concrete]);
        }
        if($single){ self::$instances[$abstract]=$object; }
        return $object;
    }
    /**
     * 解析单个构造参数
     * @param \ReflectionParameter $param
     * @return mixed
     */
    private static function ResolveParam($param){
        $type=$param->getType();
        if($type instanceof \ReflectionNamedType && !$type->isBuiltin()){
            $name=$type->getName();
            if($name==='usualtool\Lib\Temp' && self::$engine!==null){
                return self::$engine;
            }
            return self::Make($name);
        }
        if($param->getName()==='config'){
            $config=Inc::GetConfig();
            if(!empty($config)){
                return $config;
            }
        }
        if($param->isDefaultValueAvailable()){
            return $param->getDefaultValue();
        }
        if($param->allowsNull()){
            return null;
        }
        throw new \Exception("App::Make 无法解析参数：\$".$param->getName());
    }
}