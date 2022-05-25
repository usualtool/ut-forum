<?php
use library\UsualToolInc\UTInc;
use library\UsualToolData\UTData;
use library\UsualToolRoute\UTRoute;
if(UTData::ModTable("forum")):
    UTInc::GoUrl(UTRoute::Link("forum"),"");
else:
$do=UTInc::SqlCheck($_GET["do"]);
if($do=="db-save"):
   $info = file_get_contents(UTF_ROOT."/.ut.config"); 
   foreach($_POST as $k=>$v):
       $info = preg_replace("/{$k}=(.*)/","{$k}={$v}",$info); 
   endforeach;
   file_put_contents(UTF_ROOT."/.ut.config",$info);
   UTInc::GoUrl("?m=ut-frame&do=sql","数据库配置成功!");
endif;
$app->Runin(array("v","do"),array($v,$do));
$app->Open("index.cms");
endif;