<?php
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Cli;
set_time_limit(300);
ini_set('max_execution_time','300');
require dirname(__DIR__,2).'/bootstrap.php';
if(file_exists(OPEN_ROOT."/install/usualtool.lock")){
    http_response_code(404);
    Inc::Stop();
}
function task_guard($key,$label){
    $GLOBALS[$key]=false;
    register_shutdown_function(function()use($key,$label){
        if(!empty($GLOBALS[$key])){return;}
        $tail="";
        while(ob_get_level()>0){$tail=ob_get_clean().$tail;}
        $tail=trim(preg_replace("/\s+/"," ",strip_tags($tail)));
        $tail=trim(preg_replace("/(Fatal error|Parse error|Uncaught \w*Error).*$/su","",$tail));
        $err=error_get_last();
        if(!empty($err["message"])&&in_array($err["type"],array(E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR,E_USER_ERROR))){
            $tail.=" [PHP ".$err["message"]." @".basename($err["file"]).":".$err["line"]."]";
        }
        $msg=$label.($tail!="" ? "信息：".mb_substr($tail,-200) : "请查看服务器日志。");
        echo'<script>if(window.parent&&window.parent.waitFail){window.parent.waitFail('.json_encode($msg,JSON_UNESCAPED_UNICODE).');}else{alert('.json_encode($msg,JSON_UNESCAPED_UNICODE).');history.back();}</script>';
        echo'</body></html>';
    });
}
function task_fail($msg){
    $GLOBALS["task_end"]=true;
    $msg=json_encode($msg,JSON_UNESCAPED_UNICODE);
    echo'<script>if(window.parent&&window.parent.waitFail){window.parent.waitFail('.$msg.');}else{alert('.$msg.');history.back();}</script>';
    echo'</body></html>';
    exit();
}
function task_done($url,$msg){
    $GLOBALS["task_end"]=true;
    $url=json_encode($url,JSON_UNESCAPED_UNICODE);
    $msg=json_encode($msg,JSON_UNESCAPED_UNICODE);
    echo'<script>if(window.parent&&window.parent.waitDone){window.parent.waitDone('.$url.','.$msg.');}else{location.href='.$url.';}</script>';
    echo'</body></html>';
    exit();
}
$do=trim((string)($_GET["do"] ?? ""));
if($do=="db-save"):
    $info=file_get_contents(UTF_ROOT."/.ut.config");
    foreach($_POST as $k=>$v):
        if(!is_scalar($v) || !preg_match("/^[A-Za-z0-9_]+$/",(string)$k)):
            continue;
        endif;
        $info=preg_replace_callback(
            "/^".preg_quote((string)$k,"/")."=(.*?)(?=\r?$)/m",
            function($m) use ($k,$v){ return $k."=".(string)$v; },
            $info
        );
    endforeach;
    $info=preg_replace("/^ADMIN_POWER_PAGE=(.*?)(?=\r?$)/m","ADMIN_POWER_PAGE=ut-frame/admin/session",$info);
    file_put_contents(UTF_ROOT."/.ut.config",$info);
    $guard=<<<'GUARD'
<?php
namespace Controller\Ut_frame\Admin;
use usualtool\Lib\Inc;
class Session{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        http_response_code(404);
        Inc::Stop();
    }
}
GUARD;
    Inc::MakeDir(APP_ROOT."/modules/ut-frame/admin");
    file_put_contents(APP_ROOT."/modules/ut-frame/admin/session.php",$guard);
    $dbhost=trim((string)($_POST["MYSQL_HOST"] ?? "localhost"));
    $dbport=trim((string)($_POST["MYSQL_PORT"] ?? "3306"));
    $dbuser=trim((string)($_POST["MYSQL_USER"] ?? ""));
    $dbpass=(string)($_POST["MYSQL_PASS"] ?? "");
    $dbname=trim((string)($_POST["MYSQL_DB"] ?? ""));
    if($dbname!=="" && preg_match("/^[A-Za-z0-9_]+$/",$dbname) && class_exists("mysqli")){
        mysqli_report(MYSQLI_REPORT_OFF);
        $link=@mysqli_connect($dbhost,$dbuser,$dbpass,"",$dbport);
        if($link){
            @mysqli_query($link,"CREATE DATABASE IF NOT EXISTS `{$dbname}` DEFAULT CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci");
            mysqli_close($link);
        }
    }
    Inc::GoUrl("?do=module","配置完成!");
elseif($do=="db-module"):
    while(ob_get_level()>0){@ob_end_flush();}
    header('Content-Type: text/html; charset=utf-8');
    echo'<!DOCTYPE html><html><head><meta charset="utf-8"><script>window.__taskChild=true;</script></head><body>';
    echo'<span data-status="正在安装模块，请耐心等待..."></span>';
    echo str_pad('',4096);
    flush();
    task_guard("task_end","模块安装失败或中断！");
    foreach(array("authlogin","forum","payment") as $modname):
        echo'<span data-status="正在安装模块：'.$modname.'"></span>';
        flush();
        ob_start();
        Cli::Install(array("usualtool","install","module",$modname,"-1"));
        $cliout=trim(ob_get_clean());
        if(!is_dir(APP_ROOT."/modules/".$modname)):
            task_fail("安装模块 ".$modname." 失败：".mb_substr($cliout,-160));
        endif;
    endforeach;
    task_done("?do=plugin","模块安装成功!");
elseif($do=="db-plugin"):
    while(ob_get_level()>0){@ob_end_flush();}
    header('Content-Type: text/html; charset=utf-8');
    echo'<!DOCTYPE html><html><head><meta charset="utf-8"><script>window.__taskChild=true;</script></head><body>';
    echo'<span data-status="正在安装插件，请耐心等待..."></span>';
    echo str_pad('',4096);
    flush();
    task_guard("task_end","插件安装失败或中断！");
    echo'<span data-status="正在安装插件：kindeditor"></span>';
    flush();
    ob_start();
    Cli::Install(array("usualtool","install","plugin","kindeditor","-1"));
    $cliout=trim(ob_get_clean());
    if(!is_dir(APP_ROOT."/plugins/kindeditor") && !Data::QueryData("cms_plugin","","pid='kindeditor'","","1")["querynum"]):
        task_fail("安装插件失败：".mb_substr($cliout,-160));
    endif;
    task_done("?do=temp","插件安装成功!");
elseif($do=="db-temp"):
    while(ob_get_level()>0){@ob_end_flush();}
    header('Content-Type: text/html; charset=utf-8');
    echo'<!DOCTYPE html><html><head><meta charset="utf-8"><script>window.__taskChild=true;</script></head><body>';
    echo'<span data-status="正在安装模板，请耐心等待..."></span>';
    echo str_pad('',4096);
    flush();
    task_guard("task_end","模板安装失败或中断！");
    $tid=trim((string)($_POST["tid"] ?? ""));
    if(!preg_match("/^[A-Za-z0-9_\-]+$/",$tid)):
        task_fail("模板ID无效!");
    endif;
    echo'<span data-status="正在安装模板：'.$tid.'"></span>';
    flush();
    ob_start();
    Cli::Install(array("usualtool","install","template",$tid,"-1"));
    $cliout=trim(ob_get_clean());
    if(is_dir(APP_ROOT."/template/".$tid)):
        if(Data::ModTable("cms_template")):
            Data::UpdateData("cms_template",array("makefront"=>1),"tid=?",array($tid));
        endif;
        $info=preg_replace("/TEMPFRONT=(.*)/","TEMPFRONT={$tid}",file_get_contents(UTF_ROOT."/.ut.config"));
        file_put_contents(UTF_ROOT."/.ut.config",$info);
        file_put_contents(OPEN_ROOT."/install/usualtool.lock","lock");
        task_done("../","安装模板成功!");
    else:
        task_fail("安装模板失败：".mb_substr($cliout,-160));
    endif;
endif;
?>
<!DOCTYPE html>
<html>
<head>
    <title>UTForum安装</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <style>
    body{font-size:0.8rem;}
    a{color:black;text-decoration:underline;}
    p{margin-bottom:0rem;font-size:14px;}
    .wait-mask{position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,.45);z-index:9999;display:none;}
    .wait-mask.show{display:flex;align-items:center;justify-content:center;}
    .wait-box{background:#fff;border-radius:8px;padding:24px 36px;text-align:center;box-shadow:0 6px 24px rgba(0,0,0,.25);}
    .wait-spinner{width:36px;height:36px;margin:0 auto 12px;border:4px solid #e9ecef;border-top-color:#198754;border-radius:50%;animation:waitspin 1s linear infinite;}
    @keyframes waitspin{to{transform:rotate(360deg);}}
    .wait-text{font-size:14px;color:#333;}
    </style>
</head>
<body>
    <div class="wait-mask" id="waitMask"><div class="wait-box"><div class="wait-spinner"></div><div class="wait-text" id="waitText"></div></div></div>
    <iframe id="taskFrame" name="taskFrame" style="display:none;width:0;height:0;border:0;"></iframe>
    <div class="container">
        <div class="row m-b-md">
            <div class="col-md-12 mt-3 mb-3"><img src="assets/logo.png"></div>
            <div class="col-md-12">
                <div class="border p-4">
                <?php if(empty($do)): ?>
                    <div>
                    <form name="license" action="?do=config" method="post">
                    <p class="mb-2">
                        <b>基于以下协议，本软件是免费且开源的软件。所有自然人或团体组织请在所在国法律允许范围内合法使用本软件。</b>
                    </p>
                    <p class="mb-2">
                        文书1：<a target="_blank" href="//www.apache.org/licenses/LICENSE-2.0">UTForum基于Apache2.0授权，可以通过//www.apache.org/licenses/LICENSE-2.0了解或下载到详尽的协议内容。</a>
                    </p>
                    <p class="mb-2">
                        文书2：<a target="_blank" href="//github.com/usualtool/ut-forum/blob/master/LICENSE.ADD">UTForum遵循UT开源与免费协议，任何自然人与团体组织在协议约定的范围内均可免费使用。使用人应当阅读UT序言中关于文书的部分，往此去：//github.com/usualtool/ut-cms/blob/master/LICENSE.ADD。</a>
                    </p>
                    <p class="mb-3">
                        <b>你需要仔细阅读以上2份文书，在理解和同意的前提下，方可使用本软件。</b><br/><br/>
                        <b>Made in China , github.com/usualtool/ut-forum , HuangHui</b>
                    </p>
                    <p class="mb-2">
                        <input type="submit" class="btn btn-info" value="请查阅以上文书（60）" id="btn"/>
                    </p>
                    </form>
                    </div>
                    <script>
                        (function(){
                            var btn=document.getElementById("btn");
                            btn.disabled=true;
                            var time=60;
                            var timer=setInterval(function(){
                                time--;
                                if(time>=0){
                                    btn.value="请查阅以上文书（"+time+"）";
                                }else{
                                    clearInterval(timer);
                                    btn.disabled=false;
                                    btn.value="我已阅读相关文书";
                                }
                            },1000);
                        })();
                    </script>
                <?php elseif($do=="config"): ?>
                    <form action="?do=db-save" method="post" name="form">
                    <div class="border-bottom pb-2 mb-3">
                        <h4>第一步：全局配置</h4>
                        请将/app、/log、/update、/open/assets权限设置为可写并保持网络通畅。
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">应用名称:</label>
                            <input class="form-control" name="APPNAME" value="<?php echo $config["APPNAME"]; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">应用域名/IP（请带HTTP）:</label>
                            <input class="form-control" name="APPURL" value="<?php echo $config["APPURL"]; ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">数据库服务器:</label>
                            <input class="form-control" name="MYSQL_HOST" value="<?php echo !empty($config["MYSQL_HOST"]) ? $config["MYSQL_HOST"] : "localhost"; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">端口:</label>
                            <input class="form-control" name="MYSQL_PORT" value="<?php echo !empty($config["MYSQL_PORT"]) ? $config["MYSQL_PORT"] : "3306"; ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">数据库用户:</label>
                            <input class="form-control" name="MYSQL_USER" value="<?php echo $config["MYSQL_USER"]; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">数据库密码:</label>
                            <input class="form-control" name="MYSQL_PASS" value="<?php echo $config["MYSQL_PASS"]; ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">数据库名称:</label>
                            <input class="form-control" name="MYSQL_DB" value="<?php echo $config["MYSQL_DB"]; ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                        <button type="submit" class="btn btn-success">保存设置</button>
                        </div>
                    </div>
                    </form>
                <?php elseif($do=="module"): ?>
                    <p>
                        <h4>第二步：安装模块</h4>
                        将依次从官方下载安装 authlogin（登录）、forum（论坛）、payment（支付）三个模块，模块自带数据表初始化。
                    </p>
                    <hr/>
                    <a class="btn btn-success" href="javascript:void(0)" onclick="return runTask('正在安装模块，请耐心等待...','?do=db-module')">开始安装模块</a>
                <?php elseif($do=="plugin"): ?>
                    <p>
                        <h4>第三步：安装插件</h4>
                        将从官方下载安装 kindeditor（编辑器）插件。
                    </p>
                    <hr/>
                    <a class="btn btn-success" href="javascript:void(0)" onclick="return runTask('正在安装插件，请耐心等待...','?do=db-plugin')">开始安装插件</a>
                <?php elseif($do=="temp"): ?>
                    <form action="?do=db-temp" method="post" name="form" target="taskFrame">
                    <div class="border-bottom pb-2 mb-3">
                        <h4>第四步：应用模板</h4>
                        此处仅展示公开使用的模板，未选择模板将默认使用模块化模板。
                    </div>
                    <div class="row">
                    <?php
                    $data=array();
                    $temps=Inc::Auth("temp");
                    preg_match_all("/\<temp\>(.*?)\<\/temp\>/s",$temps,$tempblocks);
                    foreach($tempblocks[1] as $temp):
                        preg_match_all("/\<tid\>(.*?)\<\/tid\>/",$temp,$tid);
                        preg_match_all("/\<title\>(.*?)\<\/title\>/",$temp,$title);
                        preg_match_all("/\<picurl\>(.*?)\<\/picurl\>/",$temp,$picurl);
                        preg_match_all("/\<isfree\>(.*?)\<\/isfree\>/",$temp,$isfree);
                        if(substr($tid[1][0],0,2)=='f-' && strpos($isfree[1][0],'公开使用')!==false):
                            $data[]=array("tid"=>$tid[1][0],"title"=>$title[1][0],"picurl"=>$picurl[1][0],"isfree"=>$isfree[1][0]);
                        endif;
                    endforeach;
                    foreach($data as $rows):
                    ?>
                        <div class="col-md-3 mb-2 text-center">
                            <img src="<?php echo $rows["picurl"]; ?>" style="width:100%;" class="mb-2"><br/>
                            <input type="radio" name="tid" value="<?php echo $rows["tid"]; ?>" class="mr-1">
                            <?php echo $rows["title"]; ?>
                        </div>
                    <?php endforeach; ?>
                    </div>
                    <div class="border-top pt-3">
                        <button type="submit" class="btn btn-info mr-2" onclick="return startWait('正在安装模板，请耐心等待...')">安装（预计20秒）</button>
                    </div>
                    </form>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
    <script>
        var pollTimer=null;
        var taskFailTicks=0;
        function startWait(msg){
            document.getElementById('waitText').innerText=msg;
            document.getElementById('waitMask').classList.add('show');
            taskFailTicks=0;
            if(pollTimer){clearInterval(pollTimer);}
            pollTimer=setInterval(pollTask,500);
            return true;
        }
        function pollTask(){
            try{
                var d=document.getElementById('taskFrame').contentDocument;
                if(!d||!d.defaultView){return;}
                var list=d.querySelectorAll('[data-status]');
                if(list.length){
                    document.getElementById('waitText').innerText=list[list.length-1].getAttribute('data-status');
                }
                if(d.readyState==='complete'&&d.defaultView.__taskChild){
                    taskFailTicks++;
                    if(taskFailTicks>=4){
                        var tail=d.body?d.body.innerText.replace(/\s+/g,' ').trim():'';
                        waitFail('安装进程异常中止！'+(tail?'信息：'+tail.slice(-160):'请查看服务器日志。'));
                    }
                }else{
                    taskFailTicks=0;
                }
            }catch(e){}
        }
        function runTask(msg,url){
            startWait(msg);
            document.getElementById('taskFrame').src=url;
            return false;
        }
        function waitDone(url,msg){
            if(pollTimer){clearInterval(pollTimer);}
            document.getElementById('waitMask').classList.remove('show');
            if(msg){alert(msg);}
            window.location.href=url;
        }
        function waitFail(msg){
            if(pollTimer){clearInterval(pollTimer);}
            document.getElementById('waitMask').classList.remove('show');
            alert(msg);
        }
    </script>
</html>