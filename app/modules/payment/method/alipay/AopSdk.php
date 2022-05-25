<?php
if (!defined("AOP_SDK_WORK_DIR")){
	define("AOP_SDK_WORK_DIR", "/tmp/");
}
if (!defined("AOP_SDK_DEV_MODE")){
	define("AOP_SDK_DEV_MODE", true);
}
$lotusHome = dirname(__FILE__) . DIRECTORY_SEPARATOR . "lotusphp_runtime" . DIRECTORY_SEPARATOR;
include($lotusHome . "Lotus.php");
$lotus = new Lotus;
$lotus->option["autoload_dir"] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'aop';
$lotus->devMode = AOP_SDK_DEV_MODE;
$lotus->defaultStoreDir = AOP_SDK_WORK_DIR;
$lotus->init();