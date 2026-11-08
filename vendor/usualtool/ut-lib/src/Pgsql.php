<?php
namespace usualtool\Lib;
use usualtool\Lib\Inc;
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
 * 操作PostgreSQL
 */
class Pgsql{
    private static $PgsqlDb=null;
    /**
     * 连接PostgreSQL
     */    
    public static function GetPgsql(){
        if(self::$PgsqlDb){
            return self::$PgsqlDb;
        }
        $config=Inc::GetConfig();
        $db=@pg_connect("host=".$config["PGSQL_HOST"]." port=".$config["PGSQL_PORT"]." dbname=".$config["PGSQL_DB"]." user=".$config["PGSQL_USER"]." password=".$config["PGSQL_PASS"]);
        if(!$db){
            error_log("[UT-SQL] ".@pg_last_error());
            self::$PgsqlDb=null;
            return false;
        }
        self::$PgsqlDb=$db;
        return self::$PgsqlDb;
    }
    /**
     * 判断表是否存在
     * @param string $table
     * @return bool
     */
    public static function ModTable($table){
        $db=self::GetPgsql();
        if(!$db){
            return false;
        }
        if(!preg_match('/^[A-Za-z0-9_]+(\.[A-Za-z0-9_]+)?$/',$table)){
            return false;
        }
        $result=@pg_query($db,"select * from ".$table." limit 1");
        if($result===false){
            return false;
        }
        @pg_free_result($result);
        return true;
    }
    /**
     * 执行SQL语句
     * @param string $sql SQL语句
     * @return bool
     */
    public static function RunSql($sql){
		$db=self::GetPgsql();
        if(!$db){
            return false;
        }
        $result=@pg_query($db,$sql);
        if(!$result){
            error_log("[UT-SQL] ".@pg_last_error($db));
            return false;
        }
        return true;
    }
    /**
     * 查询数据
     * @param string $table 表名
     * @param string $field 字段
     * @param string $where 条件
     * @param string $order 排序
     * @param string|int $limit 数目
     * @param array $param 预处理值数组
     * @return array
     */
    public static function QueryData($table,$field='',$where='',$order='',$limit='',$param=array()){
        $db=self::GetPgsql();
        if(!$db){
            return array("querydata"=>array(),"curnum"=>0,"querynum"=>0);
        }
        if(!preg_match('/^[A-Za-z0-9_]+(\.[A-Za-z0-9_]+)?$/',$table)){
            return array("querydata"=>array(),"curnum"=>0,"querynum"=>0);
        }
        $field=self::CheckPart("field",empty($field) ? "*" : $field);
        if($field===false){
            $field="*";
        }
        if(!empty($where) && self::CheckPart("where",$where)===false){
            return array("querydata"=>array(),"curnum"=>0,"querynum"=>0);
        }
        if(!empty($order)){
            $order=self::CheckPart("order",$order);
            if($order===false){
                $order="";
            }
        }
        if(!empty($limit) && self::CheckPart("limit",$limit)===false){
            return array("querydata"=>array(),"curnum"=>0,"querynum"=>0);
        }
        $where=empty($where) ? "" : "where ".$where;
        $order=empty($order) ? "" : "order by ".$order;
        $limit=empty($limit) ? "" : "limit ".$limit;
        return self::RunYu("select ".$field." from ".$table." ".$where." ".$order." ".$limit,$param);
    }
    /**
     * 新增数据
     * @param string $table 被表名
     * @param array $data 字段/值数组
     * @param array $param 预处理值数组
     * @return bool 
     */
    public static function InsertData($table,$data,$param=array()){
        $db=self::GetPgsql();
        if(!$db){
            return false;
        }
        if(!is_array($data) || empty($data)){
            return false;
        }
        if(!preg_match('/^[A-Za-z0-9_]+(\.[A-Za-z0-9_]+)?$/',$table)){
            return false;
        }
        foreach(array_keys($data) as $k){
            if(self::CheckPart("column",(string)$k)===false){
                return false;
            }
        }
        $cols=array();
        foreach(array_keys($data) as $k){
            $cols[]='"'.str_replace('"','""',trim((string)$k,' "`[]')).'"';
        }
        $param=is_array($param) ? array_values($param) : array();
        $slot=0;
        foreach(array_values($data) as $v){
            if($v==="?"){ $slot++; }
        }
        if($slot!=count($param)){
            error_log("[UT-SQL] param mismatch: ".$slot." slots / ".count($param)." params");
            return false;
        }
        $values=array();
        $binds=array();
        $slotindex=0;
        foreach(array_values($data) as $v){
            $values[]="?";
            $binds[]=$v==="?" ? self::BindValue($param[$slotindex++]) : self::BindValue($v);
        }
        $sql="insert into ".$table." (".implode(",",$cols).") values (".implode(",",$values).")";
        $idcol=self::YuIdColumn($table,$db);
        if($idcol!==false){
            $sql.=' returning "'.$idcol.'"';
        }
        return self::RunYu($sql,$binds);
    }
    /**
     * 更新数据
     * @param string $table 表名
     * @param array $data 字段/值数组
     * @param string $where 条件
     * @param array $param 预处理值数组
     * @return bool
     */
    public static function UpdateData($table,$data,$where,$param=array()){
        $db=self::GetPgsql();
        if(!$db){
            return false;
        }
        if(!is_array($data) || empty($data)){
            return false;
        }
        if(!preg_match('/^[A-Za-z0-9_]+(\.[A-Za-z0-9_]+)?$/',$table)){
            return false;
        }
        foreach(array_keys($data) as $k){
            if(self::CheckPart("column",(string)$k)===false){
                return false;
            }
        }
        if(trim((string)$where)===''){
            return false;
        }
        if(self::CheckPart("where",$where)===false){
            return false;
        }
        $param=is_array($param) ? array_values($param) : array();
        $slot=0;
        foreach($data as $v){
            if($v==="?"){ $slot++; }
        }
        if(self::YuSlots($where)+$slot!=count($param)){
            error_log("[UT-SQL] param mismatch: ".(self::YuSlots($where)+$slot)." slots / ".count($param)." params");
            return false;
        }
        $updatestr='';
        $binds=array();
        $slotindex=0;
        foreach($data as $k=>$v){
            $col='"'.str_replace('"','""',trim((string)$k,' "`[]')).'"';
            if(is_string($v) && preg_match('/^[A-Za-z_][A-Za-z0-9_]*\+\d+$/',$v)){
                $updatestr.=$col."=".$v.",";
                continue;
            }
            $updatestr.=$col."=?,";
            $binds[]=$v==="?" ? self::BindValue($param[$slotindex++]) : self::BindValue($v);
        }
        $updatestr=rtrim($updatestr,',');
        $sql="update ".$table." set ".$updatestr." where ".$where;
        return self::RunYu($sql,array_merge($binds,array_slice($param,$slotindex)));
    }
    /**
     * 删除数据
     * @param string $table 表名
     * @param string $where 条件
     * @param array $param 预处理值数组
     * @return bool
     */
    public static function DelData($table,$where,$param=array()){
        $db=self::GetPgsql();
        if(!$db){
            return false;
        }
        if(!preg_match('/^[A-Za-z0-9_]+(\.[A-Za-z0-9_]+)?$/',$table)){
            return false;
        }
        if(trim((string)$where)===''){
            return false;
        }
        if(self::CheckPart("where",$where)===false){
            return false;
        }
        $param=is_array($param) ? array_values($param) : array();
        return self::RunYu("delete from ".$table." where ".$where,$param);
    }
    /**
     * 执行预处理
     * @param string $sql SQL语句
     * @param array $param 预处理值数组
     * @return array|bool|int
     */
    public static function RunYu($sql,$param=[]){
        $db=self::GetPgsql();
        $param=self::YuParams($param);
        $trimmed=ltrim($sql);
        $yutype=preg_match('/^([A-Za-z]+)/',$trimmed,$m) ? strtoupper($m[1]) : "";
        if($yutype=="WITH"){
            $yutype=preg_match('/\)\s*(insert|update|delete|replace)\b/i',$trimmed) ? "INSERT" : "SELECT";
        }
        $empty=array("querydata"=>array(),"curnum"=>0,"querynum"=>0);
        if(!$db){
            return $yutype=="SELECT" ? $empty : false;
        }
        $rewritten=self::YuRewrite($sql,count($param));
        if($rewritten===false){
            error_log("[UT-SQL] param mismatch: ".count($param)." params");
            return $yutype=="SELECT" ? $empty : false;
        }
        try{
            if($yutype=="SELECT"){
                $islimit=(bool) preg_match('/\bLIMIT\s+\d+/i',$sql);
                $total=0;
                if($islimit){
                    $countSql=self::YuCountSql($rewritten);
                    if($countSql!=false){
                        $name=self::YuStmt($countSql,$db);
                        if($name!==false){
                            $countresult=@pg_execute($db,$name,$param);
                            if($countresult){
                                $countrow=@pg_fetch_assoc($countresult);
                                $total=(int)($countrow['total'] ?? 0);
                                @pg_free_result($countresult);
                            }else{
                                error_log("[UT-SQL] ".@pg_last_error($db));
                            }
                            self::YuPoolPut($name);
                        }
                    }
                }
                $name=self::YuStmt($rewritten,$db);
                if($name===false){
                    error_log("[UT-SQL] ".@pg_last_error($db));
                    return $empty;
                }
                $result=@pg_execute($db,$name,$param);
                if(!$result){
                    self::YuPoolPut($name);
                    error_log("[UT-SQL] ".@pg_last_error($db));
                    return $empty;
                }
                $querydata=array();
                $n=0;
                while($row=@pg_fetch_assoc($result)){
                    $row["_n_"]=++$n;
                    $querydata[]=$row;
                }
                @pg_free_result($result);
                self::YuPoolPut($name);
                $curnum=count($querydata);
                if(!$islimit){
                    $total=$curnum;
                }
                return array("querydata"=>$querydata,"curnum"=>$curnum,"querynum"=>$total);
            }
            $name=self::YuStmt($rewritten,$db);
            if($name===false){
                error_log("[UT-SQL] ".@pg_last_error($db));
                return false;
            }
            $result=@pg_execute($db,$name,$param);
            if(!$result){
                self::YuPoolPut($name);
                error_log("[UT-SQL] ".@pg_last_error($db));
                return false;
            }
            if($yutype=="INSERT"){
                $row=@pg_fetch_assoc($result);
                @pg_free_result($result);
                self::YuPoolPut($name);
                if($row){
                    $firstvalue=array_values($row)[0] ?? null;
                    if($firstvalue===null){
                        return true;
                    }
                    return is_numeric($firstvalue) ? (int)$firstvalue : $firstvalue;
                }
                return true;
            }
            @pg_free_result($result);
            self::YuPoolPut($name);
            return true;
        }catch(\Throwable $e){
            if(isset($name) && is_string($name) && $name!==''){
                self::YuPoolDrop($name);
            }
            error_log("[UT-SQL] ".$e->getMessage());
            return $yutype=="SELECT" ? $empty : false;
        }
    }
    private static $YuPool=array();
    private static $YuBusy=array();
    private static $YuKeyOf=array();
    private static $YuTemp=array();
    private static $YuSeq=0;
    private static $YuMax=32;
    public static $YuReuse=true;
    /**
     * 取语句名，同SQL复用
     * @param string $sql SQL语句
     * @param object $db 连接
     * @return string|bool
     */
    public static function YuStmt($sql,$db){
        $key=md5($sql);
        if(self::$YuReuse && isset(self::$YuPool[$key]) && empty(self::$YuBusy[$key])){
            self::$YuBusy[$key]=true;
            self::$YuPool[$key]["used"]=++self::$YuSeq;
            return self::$YuPool[$key]["name"];
        }
        $name='ut'.substr($key,0,16).'_'.(++self::$YuSeq);
        $stmt=@pg_prepare($db,$name,$sql);
        if(!$stmt){
            return false;
        }
        if(!self::$YuReuse || isset(self::$YuPool[$key])){
            self::$YuTemp[$name]=true;
            return $name;
        }
        self::$YuPool[$key]=array("name"=>$name,"used"=>self::$YuSeq);
        self::$YuBusy[$key]=true;
        self::$YuKeyOf[$name]=$key;
        self::YuPoolTrim();
        if(count(self::$YuPool)>self::$YuMax){
            self::$YuTemp[$name]=true;
            unset(self::$YuKeyOf[$name]);
            unset(self::$YuBusy[$key]);
            unset(self::$YuPool[$key]);
        }
        return $name;
    }
    /**
     * 语句归还池
     * @param string $name 语句名
     */
    public static function YuPoolPut($name){
        if(isset(self::$YuKeyOf[$name])){
            unset(self::$YuBusy[self::$YuKeyOf[$name]]);
            return;
        }
        if(isset(self::$YuTemp[$name])){
            unset(self::$YuTemp[$name]);
            if(self::$PgsqlDb){
                @pg_query(self::$PgsqlDb,"DEALLOCATE ".$name);
            }
        }
    }
    /**
     * 丢弃语句
     * @param string $name 语句名
     */
    public static function YuPoolDrop($name){
        if(isset(self::$YuKeyOf[$name])){
            $key=self::$YuKeyOf[$name];
            unset(self::$YuKeyOf[$name]);
            unset(self::$YuBusy[$key]);
            unset(self::$YuPool[$key]);
            if(self::$PgsqlDb){
                @pg_query(self::$PgsqlDb,"DEALLOCATE ".$name);
            }
        }
    }
    /**
     * 清空语句池
     */
    public static function YuPoolClear(){
        if(self::$PgsqlDb){
            foreach(self::$YuPool as $item){
                @pg_query(self::$PgsqlDb,"DEALLOCATE ".$item["name"]);
            }
        }
        self::$YuPool=array();
        self::$YuBusy=array();
        self::$YuKeyOf=array();
    }
    /**
     * 池上限淘汰
     */
    private static function YuPoolTrim(){
        while(count(self::$YuPool)>self::$YuMax){
            $oldkey=null;
            $oldused=null;
            foreach(self::$YuPool as $key=>$item){
                if(!empty(self::$YuBusy[$key])){
                    continue;
                }
                if($oldused===null || $item["used"]<$oldused){
                    $oldused=$item["used"];
                    $oldkey=$key;
                }
            }
            if($oldkey===null){
                break;
            }
            if(self::$PgsqlDb){
                @pg_query(self::$PgsqlDb,"DEALLOCATE ".self::$YuPool[$oldkey]["name"]);
            }
            unset(self::$YuKeyOf[self::$YuPool[$oldkey]["name"]]);
            unset(self::$YuPool[$oldkey]);
        }
    }
    /**
     * 扫描SQL
     * @param string $sql SQL语句
     * @return array
     */
    private static function YuScan($sql){
        $out='';
        $n=0;
        $len=strlen($sql);
        $i=0;
        while($i<$len){
            $ch=$sql[$i];
            if($ch==="'"){
                $out.="'";
                $i++;
                while($i<$len){
                    $out.=$sql[$i];
                    if($sql[$i]==="'"){
                        if($i+1<$len && $sql[$i+1]==="'"){
                            $out.="'";
                            $i+=2;
                            continue;
                        }
                        $i++;
                        break;
                    }
                    $i++;
                }
                continue;
            }
            if($ch==='"' && ($i===0 || !preg_match('/[A-Za-z0-9_]/',$sql[$i-1]))){
                $out.='"';
                $i++;
                while($i<$len){
                    $out.=$sql[$i];
                    if($sql[$i]==='"'){
                        if($i+1<$len && $sql[$i+1]==='"'){
                            $out.='"';
                            $i+=2;
                            continue;
                        }
                        $i++;
                        break;
                    }
                    $i++;
                }
                continue;
            }
            if($ch==='$' && preg_match('/\$\$|\$[A-Za-z_][A-Za-z0-9_]*\$/',$sql,$m,PREG_OFFSET_CAPTURE,$i) && $m[0][1]===$i){
                $tag=$m[0][0];
                $end=strpos($sql,$tag,$i+strlen($tag));
                if($end!==false){
                    $stop=$end+strlen($tag);
                    $out.=substr($sql,$i,$stop-$i);
                    $i=$stop;
                    continue;
                }
            }
            if($ch==='-' && $i+1<$len && $sql[$i+1]==='-'){
                $out.='--';
                $i+=2;
                while($i<$len && $sql[$i]!=="\n"){
                    $out.=$sql[$i];
                    $i++;
                }
                continue;
            }
            if($ch==='/' && $i+1<$len && $sql[$i+1]==='*'){
                $depth=0;
                while($i<$len){
                    if($sql[$i]==='/' && $i+1<$len && $sql[$i+1]==='*'){
                        $depth++;
                        $out.='/*';
                        $i+=2;
                        continue;
                    }
                    if($sql[$i]==='*' && $i+1<$len && $sql[$i+1]==='/'){
                        $depth--;
                        $out.='*/';
                        $i+=2;
                        if($depth<=0){
                            break;
                        }
                        continue;
                    }
                    $out.=$sql[$i];
                    $i++;
                }
                continue;
            }
            if($ch==='?'){
                $n++;
                $out.='$'.$n;
                $i++;
                continue;
            }
            $out.=$ch;
            $i++;
        }
        return array($out,$n);
    }
    /**
     * 将 ? 转成 $n
     * @param string $sql SQL语句
     * @param int $count 参数个数
     * @return string|bool
     */
    public static function YuRewrite($sql,$count){
        $scan=self::YuScan($sql);
        if($scan[1]!=$count){
            return false;
        }
        return $scan[0];
    }
    /**
     * 统计占位符个数
     * @param string $sql SQL语句
     * @return int
     */
    public static function YuSlots($sql){
        $scan=self::YuScan($sql);
        return $scan[1];
    }
    /**
     * 绑定值归一化
     * @param array $param 预处理值数组
     * @return array
     */
    private static function YuParams($param){
        $out=array();
        foreach((is_array($param) ? $param : array()) as $value){
            $out[]=self::BindValue($value);
        }
        return $out;
    }
    /**
     * 绑定值归一化
     * @param mixed $value 原值
     * @return mixed
     */
    private static function BindValue($value){
        if($value instanceof \DateTimeInterface){
            return $value->format("Y-m-d H:i:s");
        }
        if(is_bool($value)){
            return $value ? 1 : 0;
        }
        return $value;
    }
    private static $YuIdCol=array();
    /**
     * 取表自增主键列
     * @param string $table 表名
     * @param object $db 连接
     * @return string|bool
     */
    private static function YuIdColumn($table,$db){
        if(array_key_exists($table,self::$YuIdCol)){
            return self::$YuIdCol[$table];
        }
        $col=false;
        $parts=explode('.',$table);
        foreach($parts as $k=>$v){
            $parts[$k]='"'.str_replace('"','""',$v).'"';
        }
        $quoted=self::Escape(implode('.',$parts));
        $sql="select a.attname as col from pg_index i join pg_attribute a on a.attrelid=i.indrelid and a.attnum=i.indkey[0] where i.indrelid='".$quoted."'::regclass and i.indisprimary and i.indnatts=1 and pg_get_serial_sequence('".$quoted."',a.attname) is not null limit 1";
        $query=@pg_query($db,$sql);
        if($query){
            $row=@pg_fetch_assoc($query);
            @pg_free_result($query);
            if(is_array($row) && isset($row["col"]) && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/',(string)$row["col"])){
                $col=(string)$row["col"];
            }
        }
        self::$YuIdCol[$table]=$col;
        return $col;
    }
    /**
     * 校验SQL结构片段
     * @param string $part 片段类型
     * @param string|int $value 待校验片段
     * @return string|bool
     */
    public static function CheckPart($part,$value){
        $value=(string)$value;
        switch($part){
            case "field":
                if(preg_match('/^\s*(\*|[A-Za-z0-9_.`\[\]]+(\s+(as\s+)?[A-Za-z0-9_`\[\]]+)?(\s*,\s*[A-Za-z0-9_.`\[\]]+(\s+(as\s+)?[A-Za-z0-9_`\[\]]+)?)*)\s*$/i',$value)){
                    return $value;
                }
                break;
            case "column":
                if(preg_match('/^\s*(?:"[A-Za-z_][A-Za-z0-9_]*"|`[A-Za-z_][A-Za-z0-9_]*`|\[[A-Za-z_][A-Za-z0-9_]*\]|[A-Za-z_][A-Za-z0-9_]*)(?:\.(?:"[A-Za-z_][A-Za-z0-9_]*"|`[A-Za-z_][A-Za-z0-9_]*`|\[[A-Za-z_][A-Za-z0-9_]*\]|[A-Za-z_][A-Za-z0-9_]*))?\s*$/',$value)){
                    return $value;
                }
                break;
            case "where":
                if(!preg_match('/;|--|#|\/\*/',preg_replace("/'[^']*'|\"[^\"]*\"/","",$value))){
                    return $value;
                }
                break;
            case "order":
                if(preg_match('/^\s*[A-Za-z0-9_.`\[\]]+(\s+(asc|desc))?(\s*,\s*[A-Za-z0-9_.`\[\]]+(\s+(asc|desc))?)*\s*$/i',$value)){
                    return $value;
                }
                break;
            case "limit":
                if(preg_match('/^\s*\d+(\s*,\s*\d+|\s+offset\s+\d+)?\s*$/i',$value)){
                    return $value;
                }
                break;
        }
        error_log("[UT-SQL] ".$part." rejected: ".$value);
        return false;
    }
    /**
     * 字符串转义
     * @param string $value 原值
     * @return string
     */
    public static function Escape($value){
        return str_replace("'","''",(string)$value);
    }
    public static function YuCountSql($sql) {
        $sql = rtrim($sql," \t\n\r;");
        $sql = preg_replace('/\s+LIMIT\s+\d+(?:\s*,\s*\d+)?\s*$/i','',$sql);
        $sql = preg_replace('/\s+OFFSET\s+\d+\s*$/i','',$sql);
        return "SELECT COUNT(*) AS total FROM ($sql) AS __count_wrapper";
    }
    /**
     * 对象转数组
     * @param string $obj 对象
     * @return array
     */
    public static function ObjectToArray($obj){
        if(!is_array($obj) && !is_object($obj)){
            return array();
        }
        $ret = array();
        foreach($obj as $key => $value){
            if(is_array($value) || is_object($value)){
                $ret[$key] = self::ObjectToArray($value);
            }else{
                $ret[$key] = $value;
            }
        }
        return $ret;
    }
    /**
     * 统计记录数目
     * @param string $sql SQL语句
     * @return int
     */
    public static function QueryNum($sql){
        $db=self::GetPgsql();
        if(!$db){
            return 0;
        }
        try{
            $query=@pg_query($db,$sql);
        }catch(\Throwable $e){
            error_log("[UT-SQL] ".$e->getMessage());
            return 0;
        }
        if(!$query){
            error_log("[UT-SQL] ".@pg_last_error($db));
            return 0;
        }
        return (int)@pg_num_rows($query);
    }
    /**
     * 释放内存
     * @return string 
     * @return bool
     */
    public static function FreeRes($res){
        return @pg_free_result($res);
    }
    /**
     * 返回最近的出错记录
     * @return string 
     */
    public static function ErrorMsg(){
        $db=self::GetPgsql();
        return @pg_last_error($db);
    }
    /**
     * 获取PostgreSQL版本号
     * @return array 
     */
    public static function Version(){
        $db=self::GetPgsql();
        return @pg_version($db);
    }
}