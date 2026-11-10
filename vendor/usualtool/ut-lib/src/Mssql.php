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
 * 以sqlsrv方法操作Sqlserver
 */
class Mssql{
    private static $MssqlDb=null;
    /**
     * 连接Mssql
     */    
    public static function GetMssql(){
        if(self::$MssqlDb){
            return self::$MssqlDb;
        }
        $config=Inc::GetConfig();
        $host=empty($config["MSSQL_PORT"]) ? $config["MSSQL_HOST"] : $config["MSSQL_HOST"].",".$config["MSSQL_PORT"];
        self::$MssqlDb=@sqlsrv_connect($host,array(
            'UID'=>$config["MSSQL_USER"],
            'PWD'=>$config["MSSQL_PASS"],
            'Database'=>$config["MSSQL_DB"])
        );
        if(!self::$MssqlDb){
            error_log("[UT-SQL] ".print_r(sqlsrv_errors(),true));
            self::$MssqlDb=null;
            return false;
        }
        return self::$MssqlDb;
    }
    /**
     * 判断表是否存在
     * @param string $table
     * @return bool
     */
    public static function ModTable($table){
        if(!preg_match('/^[A-Za-z0-9_]+(\.[A-Za-z0-9_]+)?$/',$table)){
            return false;
        }
        $db=self::GetMssql();
        if(!$db){
            return false;
        }
        if(sqlsrv_query($db,"select * from ".$table)===false){
            return false;
        }else{
            return true;
        }
    }
    /**
     * 执行SQL语句
     * @param string $sql SQL语句
     * @return bool
     */
    public static function RunSql($sql){
		$db=self::GetMssql();
        if(!$db){
            return false;
        }
        $result = sqlsrv_query($db,$sql);
        if(!$result){
            error_log("[UT-SQL] ".print_r(sqlsrv_errors(),true));
            return false;
        }else{
            return true;
        }
    }
    /**
     * 查询数据
     * @param string $table 被表名
     * @param string $field 字段
     * @param string $where 条件
     * @param string $order 排序
     * @param string|int $limit 数目
     * @param array $param 预处理值数组
     * @return array
     */
    public static function QueryData($table,$field='',$where='',$order='',$limit='',$param=array()){
        if(!preg_match('/^[A-Za-z0-9_]+(\.[A-Za-z0-9_]+)?$/',$table)){
            return array("querydata"=>array(),"curnum"=>0,"querynum"=>0);
        }
        $field=empty($field) ? "*" : $field;
        $field=self::CheckPart("field",$field);
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
        $top="";
        $offset="";
        if(!empty($limit)){
            if(strpos($limit,",")!==false){
                $limitarr=explode(",",$limit);
                $offset=" offset ".intval($limitarr[0])." rows fetch next ".intval($limitarr[1])." rows only";
                if(empty($order)){
                    $order="order by (select null)";
                }
            }else{
                $top="top ".intval($limit)." ";
            }
        }
        if(!self::ModTable($table)){
            return array("querydata"=>array(),"curnum"=>0,"querynum"=>0);
        }
        $sql="select ".$top.$field." from ".$table." ".$where." ".$order.$offset;
        return self::RunYu($sql,is_array($param)?$param:array());
    }
    /**
     * 新增数据
     * @param string $table 被表名
     * @param array $data 字段/值数组
     * @param array $param 预处理值数组
     * @return bool|int
     */
    public static function InsertData($table,$data,$param=array()){
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
        $paramq=is_array($param) ? array_values($param) : array();
        $slot=0;
        foreach(array_values($data) as $v){
            if($v==="?"){ $slot++; }
        }
        if(count($paramq)!=$slot){
            error_log("[UT-SQL] param mismatch: ".$slot." slots / ".count($paramq)." params");
            return false;
        }
        $cols=array();
        foreach(array_keys($data) as $k){
            $cols[]="[".str_replace("]","]]",trim((string)$k,' "`[]'))."]";
        }
        $values=array();
        $yu=array();
        $q=0;
        foreach(array_values($data) as $v){
            if($v==="?"){
                $values[]="?";
                $yu[]=$paramq[$q++];
            }elseif($v instanceof \DateTime){
                $values[]="?";
                $yu[]=$v->format("Y-m-d H:i:s");
            }elseif(is_null($v)){
                $values[]="NULL";
            }elseif(is_bool($v)){
                $values[]=$v ? "1" : "0";
            }else{
                $values[]="?";
                $yu[]=(string)$v;
            }
        }
        $sql="insert into ".$table." (".implode(",",$cols).") values (".implode(",",$values).")";
        return self::RunYu($sql,$yu);
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
        $paramq=is_array($param) ? array_values($param) : array();
        $slot=substr_count($where,"?");
        foreach(array_values($data) as $v){
            if($v==="?"){ $slot++; }
        }
        if(count($paramq)!=$slot){
            error_log("[UT-SQL] param mismatch: ".$slot." slots / ".count($paramq)." params");
            return false;
        }
        $updatestr='';
        $yu=array();
        $q=0;
        foreach($data as $k=>$v){
            $col="[".str_replace("]","]]",trim((string)$k,' "`[]'))."]";
            if($v==="?"){
                $updatestr.=$col."=?,";
                $yu[]=$paramq[$q++];
            }elseif($v instanceof \DateTime){
                $updatestr.=$col."=?,";
                $yu[]=$v->format("Y-m-d H:i:s");
            }elseif(is_null($v)){
                $updatestr.=$col."=NULL,";
            }elseif(is_bool($v)){
                $updatestr.=$col."=".($v ? 1 : 0).",";
            }elseif(is_string($v) && preg_match('/^[A-Za-z_][A-Za-z0-9_]*\+\d+$/',$v)){
                $updatestr.=$col."=".$v.",";
            }else{
                $updatestr.=$col."=?,";
                $yu[]=(string)$v;
            }
        }
        $updatestr=rtrim($updatestr,',');
        if(self::CheckPart("where",$where)===false){
            return false;
        }
        for(;$q<count($paramq);$q++){
            $yu[]=$paramq[$q];
        }
        $sql="update ".$table." set ".$updatestr." where ".$where;
        return self::RunYu($sql,$yu);
    }
    /**
     * 删除数据
     * @param string $table 表名
     * @param string $where 条件
     * @param array $param 预处理值数组
     * @return bool
     */
    public static function DelData($table,$where,$param=array()){
        if(!preg_match('/^[A-Za-z0-9_]+(\.[A-Za-z0-9_]+)?$/',$table)){
            return false;
        }
        if(trim((string)$where)===''){
            return false;
        }
        if(self::CheckPart("where",$where)===false){
            return false;
        }
        $paramq=is_array($param) ? $param : array();
        $slot=substr_count($where,"?");
        if(count($paramq)!=$slot){
            error_log("[UT-SQL] param mismatch: ".$slot." slots / ".count($paramq)." params");
            return false;
        }
        $sql="delete from ".$table." where ".$where;
        return self::RunYu($sql,$paramq);
    }
    /**
     * 执行预处理
     * @param string $sql SQL语句
     * @param array $param 预处理值数组
     * @return array|bool|int
     */
    public static function RunYu($sql,$param=[]){
        $db=self::GetMssql();
        $param=is_array($param) ? array_values($param) : array();
        $trimmed=ltrim($sql);
        $yutype=preg_match('/^([A-Za-z]+)/',$trimmed,$m) ? strtoupper($m[1]) : "";
        if($yutype=="WITH"){
            $yutype=preg_match('/\)\s*(insert|update|delete|replace)\b/i',$trimmed) ? "INSERT" : "SELECT";
        }
        $empty=array("querydata"=>array(),"curnum"=>0,"querynum"=>0);
        if(!$db){
            return $yutype=="SELECT" ? $empty : false;
        }
        try{
            if($yutype=="SELECT"){
                $islimit=(bool)(
                    preg_match('/\bTOP\s+\d+/i',$sql) ||
                    preg_match('/\bOFFSET\s+\d+\s+ROWS\s+FETCH\s+NEXT\s+\d+\s+ROWS\s+ONLY/i',$sql)
                );
                $total=0;
                if($islimit){
                    $countSql=self::YuCountSql($sql);
                    if($countSql!=false){
                        $countStmt=@sqlsrv_query($db,$countSql,$param);
                        if($countStmt===false){
                            error_log("[UT-SQL] ".print_r(sqlsrv_errors(),true));
                        }else{
                            $countrow=sqlsrv_fetch_array($countStmt,\SQLSRV_FETCH_ASSOC);
                            $total=(int)($countrow["total"] ?? 0);
                            sqlsrv_free_stmt($countStmt);
                        }
                    }
                }
                $stmt=self::YuStmt($sql,$param,$db);
                if($stmt===false){
                    error_log("[UT-SQL] ".print_r(sqlsrv_errors(),true));
                    return $empty;
                }
                if(!self::YuExecute($stmt)){
                    return $empty;
                }
                $querydata=array();
                $n=0;
                while($row=sqlsrv_fetch_array($stmt,\SQLSRV_FETCH_ASSOC)){
                    $row=self::TimeToString($row);
                    $row["_n_"]=++$n;
                    $querydata[]=$row;
                }
                self::YuPoolPut($stmt);
                $curnum=count($querydata);
                if(!$islimit){
                    $total=$curnum;
                }
                return array("querydata"=>$querydata,"curnum"=>$curnum,"querynum"=>$total);
            }
            if($yutype=="INSERT"){
                $idquery=@sqlsrv_query($db,$sql."\n;SELECT SCOPE_IDENTITY() AS id",$param);
                if($idquery===false){
                    error_log("[UT-SQL] ".print_r(sqlsrv_errors(),true));
                    return false;
                }
                $insertid=false;
                if(sqlsrv_next_result($idquery)){
                    $idrow=sqlsrv_fetch_array($idquery,\SQLSRV_FETCH_ASSOC);
                    if(is_array($idrow) && isset($idrow["id"]) && is_numeric($idrow["id"])){
                        $insertid=(int)$idrow["id"];
                    }else{
                        error_log("[UT-SQL] insert id unavailable");
                    }
                }
                sqlsrv_free_stmt($idquery);
                return $insertid===false ? true : $insertid;
            }
            $stmt=self::YuStmt($sql,$param,$db);
            if($stmt===false){
                error_log("[UT-SQL] ".print_r(sqlsrv_errors(),true));
                return false;
            }
            if(!self::YuExecute($stmt)){
                return false;
            }
            self::YuPoolPut($stmt);
            return true;
        }catch(\Throwable $e){
            if(isset($countStmt) && is_resource($countStmt)){
                self::YuPoolDrop($countStmt);
            }
            if(isset($stmt) && is_resource($stmt)){
                self::YuPoolDrop($stmt);
            }
            error_log("[UT-SQL] ".$e->getMessage());
            return $yutype=="SELECT" ? $empty : false;
        }
    }
    /**
     * 执行已准备的语句
     * @param resource $stmt 语句资源
     * @return bool
     */
    private static function YuExecute($stmt){
        if(!@sqlsrv_execute($stmt)){
            self::YuPoolDrop($stmt);
            error_log("[UT-SQL] ".print_r(sqlsrv_errors(),true));
            return false;
        }
        return true;
    }
    private static $YuPool=array();
    private static $YuBusy=array();
    private static $YuKeyOf=array();
    private static $YuSeq=0;
    private static $YuMax=32;
    public static $YuReuse=true;
    /**
     * 取语句池中的语句，同模板复用
     * @param string $sql SQL语句
     * @param array $param 预处理值数组
     * @param resource $db 连接
     * @return resource|bool
     */
    public static function YuStmt($sql,$param,$db){
        $param=is_array($param) ? array_values($param) : array();
        $poolable=true;
        foreach($param as $v){
            if(is_null($v)){
                $poolable=false;
                break;
            }
        }
        $key=null;
        if($poolable && self::$YuReuse){
            $key=self::YuKey($sql,$param,$db);
            if(isset(self::$YuPool[$key]) && empty(self::$YuBusy[$key])){
                $count=count(self::$YuPool[$key]["vals"]);
                for($i=0;$i<$count;$i++){
                    self::$YuPool[$key]["vals"][$i]=array_key_exists($i,$param) ? $param[$i] : null;
                }
                self::$YuBusy[$key]=true;
                self::$YuPool[$key]["used"]=++self::$YuSeq;
                return self::$YuPool[$key]["stmt"];
            }
        }
        $vals=array();
        $refs=array();
        $count=count($param);
        for($i=0;$i<$count;$i++){
            $vals[$i]=$param[$i];
            $refs[$i]=&$vals[$i];
        }
        $stmt=@sqlsrv_prepare($db,$sql,$refs);
        if($stmt===false){
            return false;
        }
        if(!$poolable || !self::$YuReuse || isset(self::$YuPool[$key])){
            return $stmt;
        }
        self::$YuPool[$key]=array("stmt"=>$stmt,"vals"=>$vals,"used"=>++self::$YuSeq);
        self::$YuBusy[$key]=true;
        self::$YuKeyOf[self::YuResId($stmt)]=$key;
        self::YuPoolTrim();
        if(count(self::$YuPool)>self::$YuMax){
            unset(self::$YuKeyOf[self::YuResId($stmt)]);
            unset(self::$YuBusy[$key]);
            unset(self::$YuPool[$key]);
        }
        return $stmt;
    }
    /**
     * 语句归还池
     * @param resource $stmt 语句资源
     */
    public static function YuPoolPut($stmt){
        $id=self::YuResId($stmt);
        if(isset(self::$YuKeyOf[$id])){
            unset(self::$YuBusy[self::$YuKeyOf[$id]]);
            return;
        }
        if(is_resource($stmt)){
            @sqlsrv_free_stmt($stmt);
        }
    }
    /**
     * 丢弃语句（失败路径）
     * @param resource $stmt 语句资源
     */
    public static function YuPoolDrop($stmt){
        $id=self::YuResId($stmt);
        if(isset(self::$YuKeyOf[$id])){
            $key=self::$YuKeyOf[$id];
            unset(self::$YuKeyOf[$id]);
            unset(self::$YuBusy[$key]);
            if(isset(self::$YuPool[$key])){
                if(is_resource(self::$YuPool[$key]["stmt"])){
                    @sqlsrv_free_stmt(self::$YuPool[$key]["stmt"]);
                }
                unset(self::$YuPool[$key]);
            }
            return;
        }
        if(is_resource($stmt)){
            @sqlsrv_free_stmt($stmt);
        }
    }
    /**
     * 清空语句池
     */
    public static function YuPoolClear(){
        foreach(self::$YuPool as $key=>$item){
            if(is_resource($item["stmt"])){
                @sqlsrv_free_stmt($item["stmt"]);
            }
        }
        self::$YuPool=array();
        self::$YuBusy=array();
        self::$YuKeyOf=array();
    }
    /**
     * 池上限淘汰（LRU）
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
            self::YuPoolDrop(self::$YuPool[$oldkey]["stmt"]);
        }
    }
    /**
     * 池键（SQL+参数类型+连接）
     * @param string $sql SQL语句
     * @param array $param 预处理值数组
     * @param resource $db 连接
     * @return string
     */
    private static function YuKey($sql,$param,$db){
        $types="";
        foreach($param as $v){
            if(is_int($v) || is_bool($v)){
                $types.="i";
            }elseif(is_float($v)){
                $types.="d";
            }elseif(is_null($v)){
                $types.="n";
            }else{
                $types.="s";
            }
        }
        return md5($sql."|".$types."|".self::YuResId($db));
    }
    /**
     * 资源编号
     * @param resource|object $res 资源
     * @return string|int
     */
    private static function YuResId($res){
        if(is_resource($res)){
            return (int)$res;
        }
        if(is_object($res)){
            return spl_object_hash($res);
        }
        return "0";
    }
    /**
     * 兼容旧调用：重新赋值参数
     * @param resource $stmt 语句资源
     * @param array $param 预处理值数组
     * @return bool
     */
    public static function YuBindParam($stmt,$params){
        $id=self::YuResId($stmt);
        if(!isset(self::$YuKeyOf[$id])){
            return false;
        }
        $key=self::$YuKeyOf[$id];
        $params=is_array($params) ? array_values($params) : array();
        $count=count(self::$YuPool[$key]["vals"]);
        for($i=0;$i<$count;$i++){
            self::$YuPool[$key]["vals"][$i]=array_key_exists($i,$params) ? $params[$i] : null;
        }
        return true;
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
                if(preg_match('/^\s*(?:`[A-Za-z_][A-Za-z0-9_]*`|\[[A-Za-z_][A-Za-z0-9_]*\]|[A-Za-z_][A-Za-z0-9_]*)(?:\.(?:`[A-Za-z_][A-Za-z0-9_]*`|\[[A-Za-z_][A-Za-z0-9_]*\]|[A-Za-z_][A-Za-z0-9_]*))?\s*$/',$value)){
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
                if(preg_match('/^\s*\d+(\s*,\s*\d+)?\s*$/',$value)){
                    return $value;
                }
                break;
        }
        error_log("[UT-SQL] ".$part." rejected: ".$value);
        return false;
    }
    /**
     * 字符串转义（单引号翻倍）
     * @param string $value 原值
     * @return string
     */
    public static function Escape($value){
        return str_replace("'","''",(string)$value);
    }
    /**
     * 日期对象转字符串
     * @param array $row 数据行
     * @return array
     */
    public static function TimeToString($row){
        if(!is_array($row)){
            return $row;
        }
        foreach($row as $key=>$value){
            if($value instanceof \DateTime){
                $row[$key]=$value->format("Y-m-d H:i:s");
            }
        }
        return $row;
    }

	public static function YuCountSql($sql){
		$sql = rtrim($sql," \t\n\r;");
        $sql = preg_replace('/\s+OFFSET\s+\d+\s+ROWS\s+FETCH\s+NEXT\s+\d+\s+ROWS\s+ONLY\s*$/i','',$sql);
        $sql = preg_replace('/^\s*SELECT\s+TOP\s+\d+/i','SELECT',$sql);
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
    private static $FetchRows=array();
    private static $FetchPos=0;
    /**
     * 获取结果集数组
     * @param string $obj 对象
     * @return array
     */
    public static function FetchArray($query,$type=\SQLSRV_FETCH_ASSOC){
        if(is_resource($query)){
            return sqlsrv_fetch_array($query,$type);
        }
        if(is_array($query)){
            if(self::$FetchRows!==$query){
                self::$FetchRows=$query;
                self::$FetchPos=0;
            }
            if(self::$FetchPos<count(self::$FetchRows)){
                return self::$FetchRows[self::$FetchPos++];
            }
        }
        return false;
    }
    /**
     * 对象转数组
     * @param array|object $obj 对象或数组
     * @return array
     */
    public static function ObjectToArray($obj){
        if(!is_array($obj) && !is_object($obj)){
            return array();
        }
        $ret=array();
        foreach($obj as $key => $value){
            if(is_array($value)){
                $ret[$key] = self::ObjectToArray($value);
            }elseif(is_object($value)){
                $value=(array)$value;
                $ret[$key] = self::ObjectToArray($value);
            }else{
                $ret[$key] = $value;
            }
        }
        return $ret;
    }
    /**
     * 计算某字段总和
     * @param string $table 表名
     * @param string $field 检索字段，数字类型且只能为1个
     * @param string $where 条件
     * @return int
     */
    public static function Sum($table,$field,$where=''){
        $db=self::GetMssql();
        $num="";
        if(!$db){
            return $num;
        }
        if(!preg_match('/^[A-Za-z0-9_]+(\.[A-Za-z0-9_]+)?$/',$table)){
            return $num;
        }
        $field=self::CheckPart("column",$field);
        if($field===false){
            return $num;
        }
        if(!empty($where) && self::CheckPart("where",$where)===false){
            return $num;
        }
        $where=empty($where) ? "" : "where ".$where;
        try{
            $query=@sqlsrv_query($db,"select sum(".$field.") as value from ".$table." ".$where);
            if($query===false){
                error_log("[UT-SQL] ".print_r(sqlsrv_errors(),true));
                return $num;
            }
            while($rows=self::FetchArray($query)){
                $num=$rows["value"] ?? $num;
            }
            sqlsrv_free_stmt($query);
        }catch(\Throwable $e){
            error_log("[UT-SQL] ".$e->getMessage());
        }
        return $num;
    }
    /**
     * 统计记录数目
     * @param string $sql SQL语句
     * @return int
     */
    public static function QueryNum($sql){
        $db=self::GetMssql();
        $query=sqlsrv_query($db, $sql, array(), array("Scrollable" => 'static'));
        if($query===false){
            return 0; 
        }
        return sqlsrv_num_rows($query);
    }
    /**
     * 按字段及条件检索最小值
     * @param string $table 表名
     * @param string $field 检索字段，数字类型且只能为1个
     * @param string $where 条件
     */  
    public static function Min($table,$field,$where=''){
        $db=self::GetMssql();
        $min="";
        if(!$db){
            return $min;
        }
        if(!preg_match('/^[A-Za-z0-9_]+(\.[A-Za-z0-9_]+)?$/',$table)){
            return $min;
        }
        $field=self::CheckPart("column",$field);
        if($field===false){
            return $min;
        }
        if(!empty($where) && self::CheckPart("where",$where)===false){
            return $min;
        }
        $where=empty($where) ? "" : "where ".$where;
        try{
            $query=@sqlsrv_query($db,"select min(".$field.") as value from ".$table." ".$where);
            if($query===false){
                error_log("[UT-SQL] ".print_r(sqlsrv_errors(),true));
                return $min;
            }
            while($rows=self::FetchArray($query)){
                $min=$rows["value"] ?? $min;
            }
            sqlsrv_free_stmt($query);
        }catch(\Throwable $e){
            error_log("[UT-SQL] ".$e->getMessage());
        }
        return $min;
    }
    /**
     * 按字段及条件检索最大值
     * @param string $table 表名
     * @param string $field 检索字段，数字类型且只能为1个
     * @param string $where 条件
     */  
    public static function Max($table,$field,$where=''){
        $db=self::GetMssql();
        $max="";
        if(!$db){
            return $max;
        }
        if(!preg_match('/^[A-Za-z0-9_]+(\.[A-Za-z0-9_]+)?$/',$table)){
            return $max;
        }
        $field=self::CheckPart("column",$field);
        if($field===false){
            return $max;
        }
        if(!empty($where) && self::CheckPart("where",$where)===false){
            return $max;
        }
        $where=empty($where) ? "" : "where ".$where;
        try{
            $query=@sqlsrv_query($db,"select max(".$field.") as value from ".$table." ".$where);
            if($query===false){
                error_log("[UT-SQL] ".print_r(sqlsrv_errors(),true));
                return $max;
            }
            while($rows=self::FetchArray($query)){
                $max=$rows["value"] ?? $max;
            }
            sqlsrv_free_stmt($query);
        }catch(\Throwable $e){
            error_log("[UT-SQL] ".$e->getMessage());
        }
        return $max;
    }
    /**
     * 将GBK转UTF-8
     */  
	public static function ConvertUtf8($str){
        return iconv("gbk","utf-8",$str);
    }
    /**
     * 将UTF-8转GBK
     */  
    public static function ConvertGbk($str){
        return iconv("utf-8","gbk",$str);
    }
    /**
     * 关闭Mssql连接
     */  
	public static function Close(){
        self::YuPoolClear();
        if(self::$MssqlDb){
            sqlsrv_close(self::$MssqlDb);
            self::$MssqlDb=null;
        }
    }
    /**
     * 获取版本号
     */ 
	public static function Ver(){
        $db=self::GetMssql();
        return sqlsrv_server_info($db);
    }
}