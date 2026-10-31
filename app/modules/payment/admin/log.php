<?php
namespace Controller\Payment\Admin;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Payment\Pay as PayModel;
class Log{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $app;
        $model=new PayModel();
    $pagelink="";

    $page=empty($_GET["page"]) ? 1 : ($_GET["page"] ?? 1);
    $pagenum=10;
    $minid=$pagenum*($page-1);
    $data=$model->GetLogPage($minid,$pagenum);
    $querynum=$data["querynum"];
    $querydata=$data["querydata"];
    $totalpage=ceil($querynum/$pagenum);
    $app->Runin(array("data","total","curpage","listnum","pagelink"),array($querydata,$totalpage,$page,$pagenum,$pagelink));
    $app->Open("log.cms");
    if(($_GET["do"] ?? "")=="del"){
        if(!empty($_GET["id"])):
            $ids=implode("','",Inc::SqlCheck($_GET["id"] ?? array()));
            $result=$model->DelLog("id in ('$ids')");
            if(!$result):
                Inc::GoUrl(Route::Link("payment", "log"),"删除失败!");
            else:
                Inc::GoUrl(Route::Link("payment", "log"),"删除成功!");
            endif;
        else:
            Inc::GoUrl(Route::Link("payment", "log"),"选中项为空!");
        endif;
    }
    }
}