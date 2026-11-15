<?php
namespace usualtool\Lib;
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
 * 生成模型单元测试骨架
 */
class Test{
    /**
     * 解析命令行参数
     * @param array $array
     * @return int 0=已生成 1=未生成
     */
    public static function Run($array){
        $module=isset($array[2]) ? strtolower(trim($array[2])) : "";
        $mode=0;
        foreach(array_slice($array,3) as $a){
            if(strpos((string)$a,"--")===0){ continue; }
            $mode=(int)$a;
            break;
        }
        $force=in_array("--force",$array,true);
        $db="";
        foreach($array as $a){
            if(strpos((string)$a,"--db=")===0){ $db=strtolower(trim(substr((string)$a,5))); }
        }
        if($db!=="" && !preg_match('/^[a-z0-9_]+$/',$db)){
            echo"驱动名只能包含字母、数字与下划线：".$db."\r\n";
            return 1;
        }
        if($module===""){
            echo"  用法：php usualtool test [模块名] [1/2] [--force] [--db=驱动]\r\n";
            echo"  只对能写出有效断言的方法生成，其余跳过并列出原因\r\n";
            echo"  1 = 参数级断言（Share\\Data+MockDb）\r\n";
            echo"  2 = 真库结果断言（Data::静态方法）\r\n";
            echo"  省略 1/2 时按模型能否注入自动选择\r\n";
            echo"  实参只认默认值、类型声明与 @test-args 注解，其余跳过\r\n";
            echo"  --db=pgsql 让生成物开头固定切到该驱动，省略则用 .ut.config 的 DBTYPE\r\n";
            return 1;
        }
        if(!in_array($mode,array(0,1,2),true)){
            echo"方式只能是 1 或 2\r\n";
            return 1;
        }
        return self::Make($module,$mode,$force,$db);
    }
    /**
     * 为指定模块的模型生成测试文件
     * @param string $module 模块名
     * @param int $mode 0=按模型能否注入自动选择
     * @param bool $force 覆盖已有文件、允许空骨架
     * @return int 0=已生成 1=未生成
     */
    public static function Make($module,$mode=0,$force=false,$db=""){
        $moddir=APP_ROOT."/modules/".$module;
        if(!is_dir($moddir)){
            echo"模块不存在：".$moddir."\r\n";
            return 1;
        }
        $files=glob($moddir."/model/*.php") ?: array();
        if(empty($files)){
            echo"该模块没有 model 文件：".$moddir."/model/\r\n";
            return 1;
        }
        $classes=array();
        foreach($files as $file){
            $code=file_get_contents($file);
            $short=substr(basename($file),0,-4);
            $classes[]=array(
                "short"=>$short,
                "ns"=>"Model\\".ucfirst($module)."\\".$short,
                "methods"=>self::Methods($code),
                "inject"=>(bool)preg_match('/__construct\s*\([^)]*DbInterface/',$code),
                "ctor"=>self::Ctor($code)
            );
        }
        $plan=array();
        $skipped=array();
        foreach($classes as $i=>$cls){
            $clsMode=$mode===0 ? ($cls["inject"] ? 1 : 2) : $mode;
            if(!empty($cls["ctor"])){
                $skipped[]=array("cls"=>$cls["short"],"name"=>"（整类）","why"=>"构造函数需要参数：".implode(", ",$cls["ctor"]));
                continue;
            }
            if($clsMode===1 && !$cls["inject"]){
                $skipped[]=array("cls"=>$cls["short"],"name"=>"（整类）","why"=>"构造函数未接收 DbInterface，无法注入 MockDb");
                continue;
            }
            $items=array();
            foreach($cls["methods"] as $m){
                $grade=self::Grade($m,$clsMode);
                if($grade["level"]==="skip"){
                    $skipped[]=array("cls"=>$cls["short"],"name"=>$m["name"],"why"=>$grade["why"]);
                    continue;
                }
                $items[]=array("m"=>$m,"g"=>$grade);
            }
            if(empty($items)){ continue; }
            $plan[]=array("cls"=>$cls,"mode"=>$clsMode,"items"=>$items);
        }
        if(empty($plan) && !$force){
            echo"没有可自动生成断言的方法，未写文件。\r\n";
            echo"跳过明细：\r\n";
            foreach($skipped as $sk){ echo"  ".$sk["cls"]."::".$sk["name"]."  ".$sk["why"]."\r\n"; }
            echo"如需空骨架（断言全部手填）请加 --force\r\n";
            return 1;
        }
        $dir=UTF_ROOT."/tests";
        if(!is_dir($dir) && !@mkdir($dir,0755) && !is_dir($dir)){
            echo"创建目录失败：".$dir."\r\n";
            return 1;
        }
        $outfile=$dir."/ut-".$module."-test.php";
        if(is_file($outfile) && !$force){
            echo"测试文件已存在，未覆盖：".$outfile."\r\n";
            echo"如需覆盖请加 --force\r\n";
            return 1;
        }
        $hasUnit=false;
        $hasCall=false;
        foreach($plan as $p){
            if($p["mode"]===1){ $hasUnit=true; }else{ $hasCall=true; }
        }
        $uses=array();
        if($hasUnit){ $uses[]="use Share\\Data\\MockDb;"; }
        if($hasCall){ $uses[]="use usualtool\\Lib\\Data;"; }
        foreach($plan as $p){
            $uses[]="use ".$p["cls"]["ns"].";";
        }
        $setup=array();
        if($hasUnit){ $setup[]='$db = new MockDb();'; }
        $lines=array();
        foreach($plan as $ci=>$p){
            $setup[]='$model'.($ci+1).' = new '.$p["cls"]["short"].($p["mode"]===1 ? '($db);' : '();');
        }
        $n=0;
        $unitCount=0;
        $callCount=0;
        foreach($plan as $ci=>$p){
            $idx=$ci+1;
            foreach($p["items"] as $it){
                $m=$it["m"];
                $g=$it["g"];
                $n++;
                $args=implode(", ",self::Params($m["args"],$m["body"],$m["doc"]));
                if($g["level"]==="unit"){
                    $unitCount++;
                    $seg=<<<'TPL'
echo "\n【@N@】@NAME@（参数级）\n";
$db->reset();
try {
    $r = $model@IDX@->@NAME@(@ARGS@);
    $logs = $db->calls();
    ok(count($logs) > 0, '@NAME@ 发起了数据访问', count($logs));
    $first = $logs ? $logs[0] : null;
    ok($first !== null && isset($first["args"][0]) && $first["args"][0] !== '', '@NAME@ 传了表名', $first === null ? '无调用' : json_encode($first));
    // 按下标继续断言（见《怎么使用断言》的 args 对照表）：
    // ok($first["method"] === 'Select', '实际调用的方法', $first["method"]);
    // ok($first["args"][2] === 'id=?', '条件', $first["args"][2]);
    // ok($first["args"][3] === array(1), '绑定值', json_encode($first["args"][3]));
} catch (\Throwable $e) {
    ok(false, '@NAME@ 可调用', get_class($e).': '.$e->getMessage());
}
TPL;
                }else{
                    $callCount++;
                    $assert=self::Assert($m["name"],$g["ret"]);
                    $seg=<<<'TPL'
if ($hasDb) {
echo "\n【@N@】@NAME@（结果级）\n";
try {
    $r = $model@IDX@->@NAME@(@ARGS@);
@ASSERT@
} catch (\Throwable $e) {
    $dbErr = dbErr($e);
    if ($dbErr !== '') { $hasDb = false; echo "  数据库不可用，结果级断言已跳过：$dbErr\n"; }
    else { ok(false, '@NAME@ 未抛异常', get_class($e).': '.$e->getMessage()); }
}
}
TPL;
                    $seg=str_replace("@ASSERT@",$assert,$seg);
                }
                $seg=str_replace(
                    array("@N@","@NAME@","@IDX@","@ARGS@"),
                    array($n,$m["name"],$idx,$args),
                    $seg
                );
                $lines[]=$seg;
            }
        }
        if(!empty($skipped)){
            $note=array("// ===== 未生成的方法（需手动补断言）=====");
            foreach($skipped as $sk){
                $note[]="// ".$sk["cls"]."::".$sk["name"]."  ".$sk["why"];
            }
            $lines[]=implode("\n",$note);
        }
        $desc=array();
        if($hasUnit){ $desc[]="方式 1：Share\\Data + MockDb，断言参数"; }
        if($hasCall){ $desc[]="方式 2：Data:: 静态调用，断言结果"; }
        $modeDesc=implode("；",$desc);
        $head=<<<'TPL'
<?php
/**
 * @MODULE@ 模型测试骨架（由 php usualtool test 生成）
 *
 * 生成时间：@TIME@
 * 断言方式：@MODEDESC@
 * 用法：php tests/ut-@MODULE@-test.php
 */
require dirname(__DIR__).'/autoload.php';

@DB@@USES@

$pass = 0; $fail = 0;
$hasDb = true;
function ok($cond, $msg, $extra = '') {
    global $pass, $fail;
    if ($cond) { $pass++; echo "  [OK]   $msg\n"; }
    else       { $fail++; echo "  [FAIL] $msg   $extra\n"; }
}
function dbErr($e) {
    $m = $e->getMessage();
    if (preg_match('/on (false|true|bool|null|int|string|array|float)/i', $m)) { return $m; }
    if (preg_match('/connect|refused|timed out|lost connection|gone away|unknown host|no such host|access denied|无法连接|连接失败|拒绝/i', $m)) { return $m; }
    if (stripos($m, 'undefined function') !== false) { return $m; }
    return '';
}

@SETUP@
TPL;
        $foot=<<<'TPL'
echo "\n结果：通过 $pass 条，失败 $fail 条\n";
exit($fail === 0 ? 0 : 1);
TPL;
        $dbline=($db==="" || !$hasCall) ? "" : '\\usualtool\\Lib\\Data::GetDb("'.$db.'");'."\n\n";
        if($db!=="" && !$hasCall){ echo"该模块只生成参数级断言，--db 未写入（MockDb 不连库）\r\n"; }
        $code=str_replace(
            array("@MODULE@","@TIME@","@MODEDESC@","@DB@","@USES@","@SETUP@"),
            array($module,date("Y-m-d H:i:s"),$modeDesc,$dbline,implode("\n",$uses),implode("\n",$setup)),
            $head
        );
        $code.="\n\n".implode("\n\n",$lines)."\n\n".$foot;
        $code=rtrim($code,"\n");
        if(file_put_contents($outfile,$code)===false){
            echo"写入失败，请检查 tests 目录权限\r\n";
            return 1;
        }
        echo"已生成：".$outfile."\r\n";
        echo"断言方式：".($modeDesc===""?"（无可生成方法）":$modeDesc)."\r\n";
        echo"模型 ".count($plan)." 个，生成 ".($unitCount+$callCount)." 个方法（参数级 ".$unitCount."，结果级 ".$callCount."），跳过 ".count($skipped)." 个\r\n";
        if(!empty($skipped)){
            echo"跳过明细：\r\n";
            foreach($skipped as $sk){
                echo"  ".$sk["cls"]."::".$sk["name"]."  ".$sk["why"]."\r\n";
            }
        }
        echo"运行：php tests/ut-".$module."-test.php\r\n";
        return 0;
    }
    /**
     * 提取模型里的 public 方法及其参数串
     * @param string $code
     * @return array
     */
    private static function Methods($code){
        $out=array();
        if(!preg_match_all('/public\s+function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/',$code,$m,PREG_OFFSET_CAPTURE)){
            return $out;
        }
        foreach($m[1] as $k=>$item){
            if(substr($item[0],0,2)==="__"){ continue; }
            $start=$m[0][$k][1]+strlen($m[0][$k][0]);
            $depth=1;
            $buf="";
            $len=strlen($code);
            for($i=$start;$i<$len;$i++){
                $ch=$code[$i];
                if($ch==="("){ $depth++; }
                elseif($ch===")"){ $depth--; if($depth===0){ break; } }
                if($depth>=1){ $buf.=$ch; }
            }
            $body="";
            $j=$i+1;
            while($j<$len && $code[$j]!=="{" && $code[$j]!==";"){ $j++; }
            if($j<$len && $code[$j]==="{"){
                $d=1;
                for($x=$j+1;$x<$len;$x++){
                    $c=$code[$x];
                    if($c==="{"){ $d++; }
                    elseif($c==="}"){ $d--; if($d===0){ break; } }
                    $body.=$c;
                }
            }
            $pos=$m[0][$k][1];
            $doc="";
            $pre=substr($code,max(0,$pos-800),$pos-max(0,$pos-800));
            if(preg_match('/\/\*\*((?:[^*]|\*(?!\/))*)\*\/\s*$/',$pre,$dm)){ $doc=$dm[1]; }
            $out[]=array("name"=>$item[0],"args"=>$buf,"body"=>$body,"doc"=>$doc);
        }
        return $out;
    }
    /**
     * 按顶层逗号拆分参数串
     * @param string $str
     * @return array
     */
    private static function Split($str){
        $out=array();
        $buf="";
        $depth=0;
        $len=strlen($str);
        for($i=0;$i<$len;$i++){
            $ch=$str[$i];
            if($ch==="(" || $ch==="["){ $depth++; }
            elseif($ch===")" || $ch==="]"){ $depth--; }
            if($ch==="," && $depth===0){ $out[]=$buf; $buf=""; continue; }
            $buf.=$ch;
        }
        if(trim($buf)!==""){ $out[]=$buf; }
        return $out;
    }
    /**
     * 生成调用实参：@test-args 指定值 > 默认值 > 类型声明 > 方法体的数组用法
     * @param string $raw
     * @param string $body
     * @param string $doc
     * @return array
     */
    private static function Params($raw,$body="",$doc=""){
        $fixed=self::Fixed($doc);
        if(!empty($fixed) && count($fixed)===self::ArgCount($raw)){ return $fixed; }
        $out=array();
        foreach(self::Split($raw) as $part){
            $part=trim($part);
            if($part===""){ continue; }
            $eq=strpos($part,"=");
            if($eq!==false){
                $out[]=trim(substr($part,$eq+1));
                continue;
            }
            if(!preg_match('/\$([A-Za-z_][A-Za-z0-9_]*)/',$part,$nm)){ continue; }
            $type=self::TypeOf($part);
            if($type!==""){
                $out[]=self::ZeroOf($type);
                continue;
            }
            $out[]=self::ArrayUse($nm[1],$body) ? "array()" : "null";
        }
        return $out;
    }
    /**
     * 取文档注释里 @test-args 指定的实参，没有则返回空数组
     * @param string $doc
     * @return array
     */
    private static function Fixed($doc){
        if($doc==="" || !preg_match('/@test-args[ \t]+([^\r\n*]+)/',$doc,$m)){ return array(); }
        $out=array();
        foreach(self::Split($m[1]) as $part){ $out[]=trim($part); }
        return $out;
    }
    /**
     * 参数个数
     * @param string $raw
     * @return int
     */
    private static function ArgCount($raw){
        $n=0;
        foreach(self::Split($raw) as $part){ if(trim($part)!==""){ $n++; } }
        return $n;
    }
    /**
     * 取参数的类型声明，没有则返回空串
     * @param string $part
     * @return string
     */
    private static function TypeOf($part){
        if(preg_match('/^\??([A-Za-z_\\\\][A-Za-z0-9_\\\\]*)\s+\$/',trim($part),$m)){
            return strtolower($m[1]);
        }
        return "";
    }
    /**
     * 按类型给出零值，null 只用于 mixed 与无标量类型的情况
     * @param string $type
     * @return string
     */
    private static function ZeroOf($type){
        if($type==="array" || $type==="iterable"){ return "array()"; }
        if($type==="int"){ return "0"; }
        if($type==="float"){ return "0.0"; }
        if($type==="string"){ return '""'; }
        if($type==="bool"){ return "false"; }
        return "null";
    }
    /**
     * 方法体是否把它当数组用（count/foreach/$x[ 三处证据）
     * @param string $name
     * @param string $body
     * @return bool
     */
    private static function ArrayUse($name,$body){
        $q=preg_quote($name,"/");
        return (bool)preg_match('/(count|sizeof)\s*\(\s*\$'.$q.'\b|foreach\s*\(\s*\$'.$q.'\b|\$'.$q.'\s*\[/',$body);
    }
    /**
     * 实参能否在不猜的前提下确定，不能则给出原因
     * @param array $method
     * @param string $body
     * @return string
     */
    private static function ArgWhy($method,$body){
        $raw=$method["args"];
        $fixed=self::Fixed(isset($method["doc"])?$method["doc"]:"");
        if(!empty($fixed)){
            if(count($fixed)===self::ArgCount($raw)){ return ""; }
            return "@test-args 写了 ".count($fixed)." 个值，方法有 ".self::ArgCount($raw)." 个参数";
        }
        $scalar=array("int","float","string","bool","array","iterable","mixed");
        foreach(self::Split($raw) as $part){
            $part=trim($part);
            if($part==="" || strpos($part,"=")!==false){ continue; }
            if(!preg_match('/\$([A-Za-z_][A-Za-z0-9_]*)/',$part,$nm)){ continue; }
            $name=$nm[1];
            $type=self::TypeOf($part);
            if($type!==""){
                if(in_array($type,$scalar,true)){ continue; }
                return "参数 $".$name." 类型 ".$type." 无法自动构造实参";
            }
            if(self::ArrayUse($name,$body)){ continue; }
            return "参数 $".$name." 无默认值也无类型声明，取值需人工指定";
        }
        return "";
    }
    /**
     * 取构造函数里的必需参数（无默认值）
     * @param string $code
     * @return array
     */
    private static function Ctor($code){
        if(!preg_match('/__construct\s*\(([^)]*)\)/',$code,$m)){ return array(); }
        $need=array();
        foreach(self::Split($m[1]) as $part){
            $part=trim($part);
            if($part==="" || strpos($part,"=")!==false){ continue; }
            $need[]=$part;
        }
        return $need;
    }
    /**
     * 判定单个方法能生成哪种断言，不能生成的给出原因
     * @param array $method
     * @param int $mode
     * @return array level=unit/call/skip
     */
    private static function Grade($method,$mode){
        $bare=preg_replace('/\/\*[\s\S]*?\*\//','',$method["body"]);
        $bare=preg_replace('/\/\/[^\r\n]*/','',$bare);
        if(trim($bare)===""){
            return array("level"=>"skip","why"=>"空方法，没有可断言的行为","ret"=>"");
        }
        if(preg_match('/\b(echo|print|printf|var_dump|print_r|header)\s*\(/',$bare)){
            return array("level"=>"skip","why"=>"直接输出，断言拿不到返回值","ret"=>"");
        }
        if(preg_match('/\b(exit|die)\s*[\(\;]/',$bare)){
            return array("level"=>"skip","why"=>"中途 exit，会中断测试进程","ret"=>"");
        }
        if(preg_match('/->(Runin|Open)\s*\(/',$bare)){
            return array("level"=>"skip","why"=>"控制器方法（Runin/Open 写模板），应走 HTTP 冒烟","ret"=>"");
        }
        if(preg_match('/\$_(GET|POST|REQUEST|COOKIE|SESSION|SERVER|FILES)\b/',$bare)){
            return array("level"=>"skip","why"=>"读超全局变量，输入不可控","ret"=>"");
        }
        if(preg_match('/\b(curl_init|curl_setopt|fsockopen|stream_socket_client)\b/',$bare)){
            return array("level"=>"skip","why"=>"依赖外部网络，结果不可控","ret"=>"");
        }
        if(preg_match('/\b(file_get_contents|file_put_contents|fopen|fwrite|fclose|unlink|mkdir|rmdir|scandir|glob|opendir|readdir|copy|rename|chmod|is_file|is_dir|is_writable|file_exists|ZipArchive)\b/',$bare)){
            return array("level"=>"skip","why"=>"依赖文件系统，参数需真实路径","ret"=>"");
        }
        $plain=preg_replace('/"[^"]*"|\'[^\']*\'/','',$bare);
        if(preg_match_all('/\b([A-Z][A-Z0-9_]{3,})\b/',$plain,$cm)){
            $known=array("PHP_EOL","PHP_INT_MAX","PHP_INT_SIZE","PHP_VERSION","PHP_OS","DIRECTORY_SEPARATOR","JSON_UNESCAPED_UNICODE","JSON_UNESCAPED_SLASHES","JSON_PRETTY_PRINT","JSON_BIGINT_AS_STRING","ENT_QUOTES","ENT_HTML5","SORT_REGULAR","SORT_NUMERIC","SORT_STRING","CASE_LOWER","CASE_UPPER","PREG_SPLIT_NO_EMPTY","E_ALL","E_WARNING","M_PI","PASSWORD_DEFAULT");
            foreach($cm[1] as $cname){
                if(!in_array($cname,$known,true)){
                    return array("level"=>"skip","why"=>"依赖常量 ".$cname."，CLI 下可能未定义","ret"=>"");
                }
            }
        }
        $why=self::ArgWhy($method,$bare);
        if($why!==""){ return array("level"=>"skip","why"=>$why,"ret"=>""); }
        $ret=self::RetType($bare);
        if($mode===1){
            if(strpos($bare,'$this->db')!==false || preg_match('/\bDb::[a-z]/',$bare)){
                return array("level"=>"unit","why"=>"","ret"=>$ret);
            }
            if($ret===""){ return array("level"=>"skip","why"=>"既不访问数据库，也无法推断返回形态","ret"=>""); }
            return array("level"=>"call","why"=>"","ret"=>$ret);
        }
        if(preg_match('/^(Add|Update|Insert|Create|Save|Remove|Del|Edit|Set|Post|Put|Reply|Audit|Sort|Move|Change|Init|Install|Clear|Reset|Sync|Import|Upload|Send|Bind|Write)/i',$method["name"])){
            return array("level"=>"skip","why"=>"写操作，默认不调用（避免误改数据）","ret"=>$ret);
        }
        if($ret===""){ return array("level"=>"skip","why"=>"无法静态推断返回形态，断言需手写","ret"=>""); }
        return array("level"=>"call","why"=>"","ret"=>$ret);
    }
    /**
     * 提取方法体顶层的 return 表达式（跳过闭包与分支内的 return）
     * @param string $body
     * @return array
     */
    private static function TopReturns($body){
        $out=array();
        $len=strlen($body);
        $depth=0;
        $i=0;
        while($i<$len){
            $ch=$body[$i];
            if($ch==="{" || $ch==="(" || $ch==="["){ $depth++; $i++; continue; }
            if($ch==="}" || $ch===")" || $ch==="]"){ $depth--; $i++; continue; }
            if($depth===0 && substr($body,$i,7)==="return "){
                $prev=$i>0 ? $body[$i-1] : "";
                if(!preg_match('/[A-Za-z0-9_]/',$prev)){
                    $j=$i+7;
                    $buf="";
                    $d2=0;
                    while($j<$len){
                        $c=$body[$j];
                        if($c==="(" || $c==="["){ $d2++; }
                        elseif($c===")" || $c==="]"){ $d2--; }
                        if($c===";" && $d2===0){ break; }
                        $buf.=$c;
                        $j++;
                    }
                    $out[]=$buf;
                    $i=$j+1;
                    continue;
                }
            }
            $i++;
        }
        return $out;
    }
    /**
     * 推断方法的返回形态
     * @param string $body
     * @return string array/page/row/string/bool/int/any/空
     */
    private static function RetType($body){
        $list=self::TopReturns($body);
        if(empty($list)){ return ""; }
        $types=array();
        foreach($list as $expr){
            $t=self::ExprType($expr,$body);
            if($t===""){ return ""; }
            $types[$t]=1;
        }
        if(count($types)!==1){ return ""; }
        $keys=array_keys($types);
        return $keys[0];
    }
    /**
     * 推断单个表达式的形态
     * @param string $expr
     * @param string $body
     * @return string
     */
    private static function ExprType($expr,$body=""){
        $e=preg_replace('/\s*\?\?.*$/s','',trim($expr));
        $e=trim($e);
        if($e===""){ return ""; }
        if(preg_match('/\["querydata"\]\[0\]\[[^\]]+\]\s*$/',$e)){ return ""; }
        if(preg_match('/\["querydata"\]\[0\]\s*$/',$e)){ return "row"; }
        if(preg_match('/\["querydata"\]\s*$/',$e)){ return "array"; }
        if(preg_match('/\["querynum"\]\s*$/',$e)){ return "int"; }
        if(preg_match('/^array\s*\(/i',$e) || substr($e,0,1)==="["){ return "array"; }
        if(preg_match('/(===|!==|==|!=)/',$e)){ return "bool"; }
        if(preg_match('/^Data::QueryData\s*\(/',$e)){ return "page"; }
        if(preg_match('/Data::(UpdateData|DelData)/',$e)){ return "bool"; }
        if(preg_match('/Data::InsertData/',$e)){ return "any"; }
        if($e==="true" || $e==="false"){ return "bool"; }
        if(preg_match('/^(count|intval|strlen|strtotime)\s*\(/',$e)){ return "int"; }
        if(preg_match('/^(join|implode|json_encode|str_replace|trim|md5|sha1|sprintf|substr)\s*\(/',$e)){ return "string"; }
        if(substr($e,0,1)==='"' && substr($e,-1)==='"'){ return "string"; }
        if(preg_match('/^\$([A-Za-z_][A-Za-z0-9_]*)$/',$e,$vm)){
            $v=preg_quote($vm[1],"/");
            if(preg_match('/\$'.$v.'\s*=\s*array\s*\(/',$body)){ return "array"; }
        }
        return "";
    }
    /**
     * 按返回形态生成断言行
     * @param string $name
     * @param string $ret
     * @return string
     */
    private static function Assert($name,$ret){
        if($ret==="page"){
            return "    ok(isset(\$r['querydata']) && is_array(\$r['querydata']), '".$name." 返回 Data 结构', is_array(\$r) ? implode(',', array_keys(\$r)) : gettype(\$r));";
        }
        if($ret==="row"){
            return "    ok(\$r === null || is_array(\$r), '".$name." 返回单行或 null', gettype(\$r));";
        }
        if($ret==="array"){
            return "    ok(is_array(\$r), '".$name." 返回数组', gettype(\$r));";
        }
        if($ret==="string"){
            return "    ok(is_string(\$r), '".$name." 返回字符串', gettype(\$r));";
        }
        if($ret==="bool"){
            return "    ok(is_bool(\$r), '".$name." 返回布尔', gettype(\$r));";
        }
        if($ret==="int"){
            return "    ok(is_int(\$r) || is_numeric(\$r), '".$name." 返回数值', gettype(\$r));";
        }
        return "    ok(is_int(\$r) || is_bool(\$r), '".$name." 返回数值或布尔', gettype(\$r));";
    }
}