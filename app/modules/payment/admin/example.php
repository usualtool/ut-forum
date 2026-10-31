<?php
namespace Controller\Payment\Admin;
use usualtool\Lib\Inc;
use Model\Payment\Pay as PayModel;
class Example{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $app;
        $model=new PayModel();
    $app->Runin(array("data"),array($model->GetList()));
    $app->Open("example.cms");
    }
}