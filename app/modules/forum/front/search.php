<?php
namespace Controller\Forum\Front;
use usualtool\Lib\Inc;
use Model\Forum\Forum as ForumModel;
class Search{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $app;
        $model=new ForumModel();
        $key=Inc::SqlCheck(urldecode($_GET["key"] ?? ""));
        $tags=$model->GetSearchTags();
        $pagelink="?key=".$key;

        $page=empty($_GET["page"]) ? 1 : $_GET["page"];
        $pagenum=10;
        $data=$model->SearchPosts($key,$page,$pagenum);
        $querynum=$data["querynum"];
        if($querynum==0):
            Inc::GoUrl("-1","没有搜索到有价值的信息!");
        endif;
        $querydata=$data["querydata"];
        $totalpage=$querynum==0 ? 1 : ceil($querynum/$pagenum);
        $app->Runin(array("total","curpage","listnum","pagelink"),array($totalpage,$page,$pagenum,$pagelink));
        $app->Runin(array("webplace","key","data","tags"),array("搜索:".$key,$key,$querydata,$tags));
        $app->Open("search.cms");
    }
}