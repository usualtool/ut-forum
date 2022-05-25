<?php
/**
       * --------------------------------------------------------       
       *  |    ░░░░░░░░░     █   █░▀▀█▀▀░    ░░░░░░░░░      |           
       *  |  ░░░░░░░         █▄▄▄█   █                      |            
       *  |                                                 |            
       *  | Author:HuangDou   Email:292951110@qq.com        |            
       *  | QQ-Group:583610949                              |           
       *  | WebSite:http://www.UsualTool.com                |            
       *  | UT Framework is suitable for Apache2 protocol.  |            
       * --------------------------------------------------------                
*/
ini_set("error_reporting","E_ALL & ~E_NOTICE");
/**
 * 开启session
 */
session_start();
/**
 * 支付模块独立设置
 * 根路径必为UTF_ROOT
 */
define('UTF_ROOT',dirname(dirname(dirname(dirname(dirname(__FILE__))))));
/**
 * 支付路径
 */
define('PAY_PATH',dirname(__FILE__));
/**
 * 类加载容错
 */
require UTF_ROOT.'/'.'/library/UsualToolInc.php';
require UTF_ROOT.'/'.'/library/UsualToolData.php';
/**
 * 加载配置
 */
$config=library\UsualToolInc\UTInc::GetConfig();