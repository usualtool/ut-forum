<?php
namespace usualtool\Lib;
use usualtool\Lib\Inc;
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
 * 全局权限中间件
 * FRONT_POWER_PAGE/ADMIN_POWER_PAGE 前/后端权限文件
 * FRONT_POWER_OUT/ADMIN_POWER_OUT 前/后端豁免白名单文件
 * 文件格式：模块/端/页面名称
 */
class Power{
    protected $deve;
    protected $path;
    protected $page;
    /**
     * 构造函数
     * @param bool $deve 前/后端判断
     * @param string $path 模块路径 (MODULE_PATH)
     */
    public function __construct($deve,$path){
        $this->deve = $deve;
        $this->path = $path;
    }
    /**
     * 设置当前页面标识
     */
    public function SetPage($page){
        $this->page = $page;
        return $this;
    }
    /**
     * 执行权限检查
     * @return bool 返回 true 表示通过，false 表示被拦截
     */
    public function Check(){
        global $app,$m,$p,$config;
        $power_page=$this->deve ? "ADMIN_POWER_PAGE" : "FRONT_POWER_PAGE";
        $power_out=$this->deve ? "ADMIN_POWER_OUT" : "FRONT_POWER_OUT";
        $_power_page_=$config[$power_page] ?? '';
        $_power_out_=$config[$power_out] ?? '';
        if(!empty($_power_page_) && !Inc::Contain($this->page,explode(",",$_power_out_))){
            $_auth_=$this->path."/".$_power_page_.".php";
            if(file_exists($_auth_)){
                $_power_cls_=Inc::GetClassName($_auth_);
                if($_power_cls_!==null){
                    require_once $_auth_;
                    $_power_ctrl_=new $_power_cls_();
                    if(method_exists($_power_ctrl_,"index")||method_exists($_power_ctrl_,"__call")){
                        $_power_ctrl_->index();
                    }
                }else{
                    require_once $_auth_;
                }
            }else{
                Debug::Error("view",$this->path."/".$_power_page_);
            }
        }else{
            return true;
        }
    }
}