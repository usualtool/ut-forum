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
 * 控制器基类
 */
class Controller{
    /**
     * 惰性属性
     * @param string $name
     * @return mixed
     */
    public function __get($name){
        if($name==='app'){
            return App::Engine();
        }
        if($name==='config'){
            return Inc::GetConfig();
        }
        return null;
    }
    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name){
        return $name==='app' || $name==='config';
    }
    /**
     * 显式取引擎
     * @return object|null
     */
    protected function App(){
        return App::Engine();
    }
    /**
     * 显式取配置
     * @param string|null $key
     * @return mixed
     */
    protected function Config($key=null){
        return Inc::Config($key);
    }
}