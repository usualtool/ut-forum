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
 * 操作Sqlite
 */
class Sqlite{
    private static $SqliteDb=null;
    /**
     * 连接Sqlite
     */    
    public static function GetSqlite(){
        if(self::$SqliteDb){
            return self::$SqliteDb;
        }
        $config=Inc::GetConfig();
        $file=isset($config["SQLITE_DB"]) ? $config["SQLITE_DB"] : "";
        if($file===""){
            return false;
        }
        try{
            self::$SqliteDb=new \SQLite3($file);
            @self::$SqliteDb->exec("pragma journal_mode=WAL");
            @self::$SqliteDb->exec("pragma synchronous=NORMAL");
            @self::$SqliteDb->busyTimeout(5000);
        }catch(\Throwable $e){
            error_log("[UT-SQL] ".$e->getMessage());
            self::$SqliteDb=null;
            return false;
        }
        return self::$SqliteDb;
    }
    /**
     * 判断表是否存在
     * @param string $table
     * @return bool
     */
    public static function ModTable($table){
        $db=self::GetSqlite();
        if(!$db){
            return false;
        }
        if(!preg_match('/^[A-Za-z0-9_]+(\.[A-Za-z0-9_]+)?$/',$table)){
            return false;
        }
        $num=@$db->querySingle("select count(*) from sqlite_master where type='table' and name='".self::Escape($table)."'");
        return $num>0;
    }
    /**
     * 执行SQL语句
     * @param string $sql SQL语句
     * @return bool
     */
    public static function RunSql($sql){
		$db=self::GetSqlite();
        if(!$db){
            return false;
        }
        try{
            $result=$db->exec($sql);
        }catch(\Throwable $e){
            error_log("[UT-SQL] ".$e->getMessage());
            return false;
        }
        if($result===false){
            error_log("[UT-SQL] ".$db->lastErrorMsg());
            return false;
        }
        return true;
    }
    /**
     * 获取单个或多个数据
     * @param string $table 表名
     * @param string $field 字段
     * @param string $where 条件
     * @param string $order 排序
     * @param string|int $limit 数目
     * @param array $param 预处理值数组
     * @return array
     */
    public static function QueryData($table,$field='',$where='',$order='',$limit='',$param=array()){
        $db=self::GetSqlite();
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
        $limit=(string)$limit;
        if($limit!=="" && self::CheckPart("limit",$limit)===false){
            return array("querydata"=>array(),"curnum"=>0,"querynum"=>0);
        }
        $where=empty($where) ? "" : "where ".$where;
        $order=empty($order) ? "" : "order by ".$order;
        $limit=$limit==="" ? "" : "limit ".$limit;
        return self::RunYu("select ".$field." from ".$table." ".$where." ".$order." ".$limit,$param);
    }
    /**
     * 新增数据
     * @param string $table 表名
     * @param string $data 字段/值数组
     * @param string $param 预处理值数组
     * @return bool 
     */
    public static function InsertData($table,$data,$param=array()){
        $db=self::GetSqlite();
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
        $db=self::GetSqlite();
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
        $db=self::GetSqlite();
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
        $db=self::GetSqlite();
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
        if(self::YuSlots($sql)!=count($param)){
            error_log("[UT-SQL] param mismatch: ".self::YuSlots($sql)." slots / ".count($param)." params");
            return $yutype=="SELECT" ? $empty : false;
        }
        try{
            if($yutype=="SELECT"){
                $islimit=(bool) preg_match('/\bLIMIT\b/i',$sql);
                $total=0;
                if($islimit){
                    $countSql=self::YuCountSql($sql);
                    if($countSql!=false){
                        $countStmt=self::YuStmt($countSql,$param,$db);
                        if($countStmt!==false){
                            $countresult=@$countStmt->execute();
                            if($countresult){
                                $countrow=$countresult->fetchArray(SQLITE3_NUM);
                                $total=(int)($countrow[0] ?? 0);
                                $countresult->finalize();
                            }else{
                                error_log("[UT-SQL] ".$db->lastErrorMsg());
                            }
                            self::YuPoolPut($countStmt);
                        }
                    }
                }
                $stmt=self::YuStmt($sql,$param,$db);
                if($stmt===false){
                    error_log("[UT-SQL] ".$db->lastErrorMsg());
                    return $empty;
                }
                $result=@$stmt->execute();
                if(!$result){
                    self::YuPoolPut($stmt);
                    error_log("[UT-SQL] ".$db->lastErrorMsg());
                    return $empty;
                }
                $querydata=array();
                $n=0;
                while($row=$result->fetchArray(SQLITE3_ASSOC)){
                    $row["_n_"]=++$n;
                    $querydata[]=$row;
                }
                $result->finalize();
                self::YuPoolPut($stmt);
                $curnum=count($querydata);
                if(!$islimit){
                    $total=$curnum;
                }
                return array("querydata"=>$querydata,"curnum"=>$curnum,"querynum"=>$total);
            }
            $stmt=self::YuStmt($sql,$param,$db);
            if($stmt===false){
                error_log("[UT-SQL] ".$db->lastErrorMsg());
                return false;
            }
            $result=@$stmt->execute();
            if(!$result){
                self::YuPoolPut($stmt);
                error_log("[UT-SQL] ".$db->lastErrorMsg());
                return false;
            }
            $result->finalize();
            self::YuPoolPut($stmt);
            if($yutype=="INSERT"){
                if($db->changes()<1){
                    return true;
                }
                $insertid=$db->lastInsertRowID();
                return $insertid ? $insertid : true;
            }
            return true;
        }catch(\Throwable $e){
            if(isset($countStmt) && is_object($countStmt)){
                self::YuPoolDrop($countStmt);
            }
            if(isset($stmt) && is_object($stmt)){
                self::YuPoolDrop($stmt);
            }
            error_log("[UT-SQL] ".$e->getMessage());
            return $yutype=="SELECT" ? $empty : false;
        }
    }
    private static $YuPool=array();
    private static $YuBusy=array();
    private static $YuKeyOf=array();
    private static $YuSeq=0;
    private static $YuMax=32;
    public static $YuReuse=true;
    /**
     * 取SQL语句复用
     * @param string $sql SQL语句
     * @param array $param 预处理值数组
     * @param object $db 连接
     * @return object|bool
     */
    public static function YuStmt($sql,$param,$db){
        $key=md5($sql."|".self::YuResId($db));
        if(self::$YuReuse && isset(self::$YuPool[$key]) && empty(self::$YuBusy[$key])){
            $stmt=self::$YuPool[$key]["stmt"];
            @$stmt->reset();
            if(method_exists($stmt,"clear")){
                @$stmt->clear();
            }
            self::$YuBusy[$key]=true;
            self::$YuPool[$key]["used"]=++self::$YuSeq;
        }else{
            $stmt=@$db->prepare($sql);
            if(!$stmt){
                return false;
            }
            if(self::$YuReuse && !isset(self::$YuPool[$key])){
                self::$YuPool[$key]=array("stmt"=>$stmt,"used"=>++self::$YuSeq);
                self::$YuBusy[$key]=true;
                self::$YuKeyOf[self::YuResId($stmt)]=$key;
                self::YuPoolTrim();
                if(count(self::$YuPool)>self::$YuMax){
                    self::YuPoolDrop($stmt);
                }
            }
        }
        $params=is_array($param) ? array_values($param) : array();
        foreach($params as $i=>$value){
            @$stmt->bindValue($i+1,$value);
        }
        return $stmt;
    }
    /**
     * 语句归还池
     * @param object $stmt 语句
     */
    public static function YuPoolPut($stmt){
        $id=self::YuResId($stmt);
        if(isset(self::$YuKeyOf[$id])){
            unset(self::$YuBusy[self::$YuKeyOf[$id]]);
        }
    }
    /**
     * 丢弃语句
     * @param object $stmt 语句
     */
    public static function YuPoolDrop($stmt){
        $id=self::YuResId($stmt);
        if(isset(self::$YuKeyOf[$id])){
            $key=self::$YuKeyOf[$id];
            unset(self::$YuKeyOf[$id]);
            unset(self::$YuBusy[$key]);
            unset(self::$YuPool[$key]);
        }
    }
    /**
     * 清空语句池
     */
    public static function YuPoolClear(){
        self::$YuPool=array();
        self::$YuBusy=array();
        self::$YuKeyOf=array();
    }
    /**
     * 池上限淘汰，LRU
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
            unset(self::$YuKeyOf[self::YuResId(self::$YuPool[$oldkey]["stmt"])]);
            unset(self::$YuPool[$oldkey]);
        }
    }
    /**
     * 资源编号
     * @param object $res 资源
     * @return string|int
     */
    private static function YuResId($res){
        if(is_object($res)){
            return spl_object_hash($res);
        }
        if(is_resource($res)){
            return (int)$res;
        }
        return "0";
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
     * 统计占位符个数
     * @param string $sql SQL语句
     * @return int
     */
    public static function YuSlots($sql){
        $len=strlen($sql);
        $n=0;
        $i=0;
        while($i<$len){
            $ch=$sql[$i];
            if($ch==="'" || $ch==='"' || $ch==='`'){
                $i++;
                while($i<$len){
                    if($sql[$i]===$ch){
                        if($i+1<$len && $sql[$i+1]===$ch){
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
            if($ch==='['){
                while($i<$len && $sql[$i]!==']'){
                    $i++;
                }
                $i++;
                continue;
            }
            if($ch==='-' && $i+1<$len && $sql[$i+1]==='-'){
                while($i<$len && $sql[$i]!=="\n"){
                    $i++;
                }
                continue;
            }
            if($ch==='/' && $i+1<$len && $sql[$i+1]==='*'){
                $i+=2;
                while($i<$len && !($sql[$i]==='*' && $i+1<$len && $sql[$i+1]==='/')){
                    $i++;
                }
                $i+=2;
                continue;
            }
            if($ch==='?'){
                $n++;
            }
            $i++;
        }
        return $n;
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
     * 字符串转义，单引号翻倍
     * @param string $value 原值
     * @return string
     */
    public static function Escape($value){
        return str_replace("'","''",(string)$value);
    }
    public static function YuCountSql($sql){
        $sql = rtrim($sql," \t\n\r;");
        $sql = preg_replace('/\s+LIMIT\s+\d+(?:\s*,\s*\d+|\s+OFFSET\s+\d+)?\s*$/i','',$sql);
        $sql = rtrim(self::YuCutOrder($sql)," \t\n\r");
        if(preg_match('/--[^\n]*$/',$sql)){
            $sql.="\n";
        }
        return "SELECT COUNT(*) AS total FROM ($sql) AS __count_wrapper";
    }
    private static function YuCutOrder($sql){
        $len=strlen($sql);
        $depth=0;
        $cut=-1;
        $quote='';
        for($i=0;$i<$len;$i++){
            $ch=$sql[$i];
            if($quote!==''){
                if($quote==='['){
                    if($ch===']'){
                        $quote='';
                    }
                }elseif($ch===$quote){
                    if($i+1<$len && $sql[$i+1]===$quote){
                        $i++;
                    }else{
                        $quote='';
                    }
                }
                continue;
            }
            if($ch==="'" || $ch==='"' || $ch==='`' || $ch==='['){
                $quote=$ch;
                continue;
            }
            if($ch==='-' && $i+1<$len && $sql[$i+1]==='-'){
                while($i+1<$len && $sql[$i+1]!=="\n"){
                    $i++;
                }
                continue;
            }
            if($ch==='/' && $i+1<$len && $sql[$i+1]==='*'){
                $i+=2;
                while($i+1<$len && !($sql[$i]==='*' && $sql[$i+1]==='/')){
                    $i++;
                }
                $i++;
                continue;
            }
            if($ch==='('){
                $depth++;
                continue;
            }
            if($ch===')'){
                if($depth>0){
                    $depth--;
                }
                continue;
            }
            if($depth===0 && ($ch==='o' || $ch==='O') && ($i===0 || !preg_match('/[A-Za-z0-9_]/',$sql[$i-1])) && preg_match('/\GORDER\s+BY\b/i',$sql,$m,0,$i)){
                $cut=$i;
                $i+=strlen($m[0])-1;
            }
        }
        if($cut>0){
            $sql=substr($sql,0,$cut);
        }
        return $sql;
    }
    /**
     * 统计记录数目
     * @param string $sql SQL语句
     * @return int
     */
    public static function QueryNum($sql){
        $db=self::GetSqlite();
        if(!$db){
            return 0;
        }
        $sql=trim((string)$sql);
        if($sql===''){
            return 0;
        }
        try{
            if(preg_match('/^(select|with)\s/i',$sql)){
                $inner=rtrim($sql," \t\n\r;");
                if(preg_match('/--[^\n]*$/',$inner)){
                    $inner.="\n";
                }
                $total=@$db->querySingle("select count(*) as total from (".$inner.") as __num_wrapper");
                if($total!==false && $total!==null){
                    return (int)$total;
                }
            }
            $result=@$db->query($sql);
        }catch(\Throwable $e){
            error_log("[UT-SQL] ".$e->getMessage());
            return 0;
        }
        if(!is_object($result)){
            error_log("[UT-SQL] ".$db->lastErrorMsg());
            return 0;
        }
        $num=0;
        while($result->fetchArray(SQLITE3_NUM)){
            $num++;
        }
        $result->finalize();
        return $num;
    }
    /**
     * 返回最近的出错记录
     * @return string 
     */
    public static function ErrorMsg(){
        $db=self::GetSqlite();
        return $db ? $db->lastErrorMsg() : "";
    }
    /**
     * 获取SQLITE版本号
     * @return array 
     */
    public static function Version(){
        $db=self::GetSqlite();
        return $db ? $db->version() : array();
    }
}