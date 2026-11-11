<?php
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
 * 助手函数
 */
namespace{
    if(!function_exists('app')){
        /**
         * 读取引擎实例
         * @return \usualtool\Lib\Temp|null
         */
        function app(){
            return \usualtool\Lib\App::Engine();
        }
    }
    if(!function_exists('make')){
        /**
         * 从容器解析实例（自动注入依赖）
         * @param string $abstract 类名或抽象名
         * @return object
         */
        function make($abstract){
            return \usualtool\Lib\App::Make($abstract);
        }
    }
    if(!function_exists('config')){
        /**
         * 取全局配置
         * @param string|null $key 键名
         * @return mixed
         */
        function config($key=null){
            return \usualtool\Lib\Inc::Config($key);
        }
    }
}