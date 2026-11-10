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
 * 以mysqli方法操作Mysql
 */
class Mysql{
    /**
     * 获取配置
     */
    public static function GetConfig(){
        return Inc::GetConfig();
    }
    /**
     * 连接Mysqli
     */    
    public static function GetMysql(){
        static $db=null;
        if($db instanceof \mysqli){
            return $db;
        }
        $config=self::GetConfig();
        if(empty($config["MYSQL_DB"]) || empty($config["MYSQL_USER"])){
            $msg="请检查.ut.config Mysql数据库配置";
            if(PHP_SAPI==="cli"){
                echo $msg."\r\n";
                exit();
            }
            http_response_code(503);
            header('Content-Type: text/html; charset=utf-8');
            echo'<pre style="padding:24px;font:14px/1.8 monospace">'.$msg.'</pre>';
            exit;
        }
        $host=empty($config["MYSQL_PORT"]) ? $config["MYSQL_HOST"] : $config["MYSQL_HOST"].":".$config["MYSQL_PORT"];
        try{
            $db=new \mysqli($host,$config["MYSQL_USER"],$config["MYSQL_PASS"],$config["MYSQL_DB"]);
        }catch(\mysqli_sql_exception $e){
            $msg=$e->getMessage();
            if(PHP_SAPI==="cli"){
                echo $msg."\r\n";
                exit();
            }
            http_response_code(503);
            header('Content-Type: text/html; charset=utf-8');
            echo'<pre style="padding:24px;font:14px/1.8 monospace">'.$msg.'</pre>';
            exit;
        }
        if(!$db){
            return "Mysqli connection error.";
        }else{
            try{
                $db->set_charset($config["MYSQL_CHARSET"]);
            }catch(\mysqli_sql_exception $e){
                error_log("[UT-SQL] ".$e->getMessage());
            }
            return $db;
        }
    }
    /**
     * 判断表是否存在
     * @param string $table
     * @return bool
     */
    public static function ModTable($table){
        static $mods=array();
        if(!isset($mods[$table])){
            $db=self::GetMysql();
            try{
                $mods[$table]=mysqli_num_rows($db->query("SHOW TABLES LIKE '". $table."'"))==1 ? true : false;
            }catch(\mysqli_sql_exception $e){
                error_log("[UT-SQL] ".$e->getMessage());
                $mods[$table]=false;
            }
        }
        return $mods[$table];
    }
    /**
     * Mysqli批量任务
     * @param string $sql SQL语句
     * @return bool
     */
    public static function RunSql($sql){
        $db=self::GetMysql();
        $ret=false;
        try{
            $ret=$db->multi_query($sql);
            if($ret){
                while($db->more_results()){
                    if($db->next_result()){
                        $res=$db->store_result();
                        if($res instanceof \mysqli_result){ $res->free(); }
                    }
                }
                if($db->errno){ $ret=false; }
            }
        }catch(\mysqli_sql_exception $e){
            $ret=false;
        }
        return $ret;
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
        $limit=empty($limit) ? "" : "limit ".$limit;
        if(self::ModTable($table)){
            $sql="select ".$field." from `".$table."` ".$where." ".$order." ".$limit;
            return self::RunYu($sql,is_array($param)?$param:array());
        }else{
            return array("querydata"=>array(),"curnum"=>0,"querynum"=>0);
        }
    }
    /**
     * 添加数据
     * @param string $table 表名
     * @param array $data 字段/数据数组
     * @param array $param 预处理值数组
     * @return bool
     */
    public static function InsertData($table,$data,$param=array()){
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
        $values=array();
        $yu=array();
        $q=0;
        foreach(array_values($data) as $v){
            if($v==="?"){
                $values[]="?";
                $yu[]=$paramq[$q++];
            }elseif(is_null($v)){
                $values[]="NULL";
            }elseif(is_bool($v)){
                $values[]=$v ? "1" : "0";
            }else{
                $values[]="?";
                $yu[]=(string)$v;
            }
        }
        $valstr=empty($values) ? "''" : implode(",",$values);
        $sql="insert into `".$table."` (".implode(',',array_keys($data)).") values (".$valstr.")";
        return self::RunYu($sql,$yu);
    }
    /**
     * 编辑数据
     * @param string $table 表名
     * @param array $data 字段/数据数组
     * @param string $where 条件
     * @param array $param 预处理值数组
     * @return bool
     */
    public static function UpdateData($table,$data,$where,$param=array()){
        foreach(array_keys($data) as $k){
            if(self::CheckPart("column",(string)$k)===false){
                return false;
            }
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
        if(!empty($data)){
            foreach($data as $k=>$v){
                if($v==="?"){
                    $updatestr.=$k."=?,";
                    $yu[]=$paramq[$q++];
                }elseif(is_null($v)){
                    $updatestr.=$k."=NULL,";
                }elseif(is_bool($v)){
                    $updatestr.=$k."=".($v ? 1 : 0).",";
                }elseif(preg_match('/^[A-Za-z_][A-Za-z0-9_]*\+\d+$/',$v)){
                    $updatestr.=$k."=".$v.",";
                }else{
                    $updatestr.=$k."=?,";
                    $yu[]=(string)$v;
                }
            }
            $updatestr=rtrim($updatestr,',');
        }
        if(self::CheckPart("where",$where)===false){
            return false;
        }
        for(;$q<count($paramq);$q++){
            $yu[]=$paramq[$q];
        }
        $sql="update `".$table."` set ".$updatestr." where ".$where;
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
        if(self::CheckPart("where",$where)===false){
            return false;
        }
        $paramq=is_array($param) ? $param : array();
        $slot=substr_count($where,"?");
        if(count($paramq)!=$slot){
            error_log("[UT-SQL] param mismatch: ".$slot." slots / ".count($paramq)." params");
            return false;
        }
        $sql="delete from `".$table."` where ".$where;
        return self::RunYu($sql,$paramq);
    }
    /**
     * 复制数据
     * @param string $table 表名
     * @param string $where 条件
     * @param array $param 预处理值数组
     * @return bool
     */
    public static function CopyData($table, $where = '', $param = array()) {
        $db = self::GetMysql();
        if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
            return false;
        }
        $where = trim((string)$where);
        if ($where !== '' && self::CheckPart("where", $where) === false) {
            return false;
        }
        if (!is_array($param)) {
            $param = array();
        }
        try {
            $result = $db->query("SHOW COLUMNS FROM `".$table."` WHERE `Extra` LIKE '%auto_increment%'");
            $autoCols = array();
            if($result){
                while ($row = $result->fetch_assoc()) {
                    $autoCols[] = $row['Field'];
                }
            }
            $sql = "SELECT * FROM `".$table."`".($where === '' ? '' : " WHERE ".$where);
            if(substr_count($sql, "?") != count($param)){
                return false;
            }
            $stmt = self::YuStmt($sql, $param, $db);
            if (!$stmt) return false;
            if (!empty($param)) self::YuBindParam($stmt, $param);
            if (!$stmt->execute()) { self::YuPoolDrop($stmt); return false; }
            $query = $stmt->get_result();
            if (!$query) { self::YuPoolDrop($stmt); return false; }
            
            $rowlist = array();
            while($row = $query->fetch_assoc()){
                $rowlist[] = $row;
            }
            $query->free();
            self::YuPoolPut($stmt);
            if (empty($rowlist)) return false;
            $cols = array();
            $fields = array();
            foreach ($rowlist[0] as $key => $value) {
                if (in_array($key, $autoCols, true)) continue;
                $cols[] = $key;
                $fields[] = "`".str_replace("`", "``", $key)."`";
            }
            if (empty($cols)) return false;
            $vals = array();
            foreach($rowlist as $rows){
                $line = array();
                foreach ($cols as $col) {
                    $value = array_key_exists($col, $rows) ? $rows[$col] : null;
                    if ($value === null) {
                        $line[] = "NULL";
                    } else {
                        $line[] = "'".$db->real_escape_string((string)$value)."'";
                    }
                }
                $vals[] = "(".implode(",", $line).")";
            }
            $prefix = "INSERT INTO `".$table."` (".implode(",", $fields).") VALUES ";
            $chunkSize = 500;
            $newids = array();
            for($i = 0; $i < count($vals); $i += $chunkSize){
                $batch = array_slice($vals, $i, $chunkSize);
                $sql = $prefix . implode(",", $batch);
                $res = self::RunSql($sql);
                if ($res === false) continue;
                $firstId = (int)$db->insert_id;
                for ($j = 0; $j < count($batch); $j++) {
                    $newids[] = $firstId + $j;
                }
            }
            return empty($newids) ? false : (count($newids) === 1 ? $newids[0] : $newids);
        } catch (\mysqli_sql_exception $e) {
            error_log("[UT-SQL] ".$e->getMessage());
            return false;
        }
    }
	/**
	 * 执行预处理
	 * @param string $sql SQL语句
     * @param array $param 预处理值数组
	 * @return array|bool|int
	 */
    public static function RunYu($sql,$param=[]){
        $db=self::GetMysql();
        $trimmed=ltrim($sql);
        $yutype=preg_match('/^([A-Za-z]+)/',$trimmed,$m) ? strtoupper($m[1]) : "";
        if($yutype=="WITH"){
            $yutype=preg_match('/\)\s*(insert|update|delete|replace)\b/i',$trimmed) ? "INSERT" : "SELECT";
        }
        try{
            if($yutype=="SELECT"){
                $islimit=(bool) preg_match('/\bLIMIT\b/i',$sql);
                $limitrows=0;
                if($islimit && preg_match('/\bLIMIT\s+(\d+)(?:\s*,\s*(\d+))?\s*$/i',$sql,$limitmatch)){
                    if(isset($limitmatch[2])){
                        $limitrows=((int)$limitmatch[1]==0) ? (int)$limitmatch[2] : 0;
                    }else{
                        $limitrows=(int)$limitmatch[1];
                    }
                }
                $stmt=self::YuStmt($sql,$param,$db);
                if(!$stmt){
                    return ["querydata" => [], "curnum" => 0, "querynum" => 0];
                }
                if(!empty($param)){
                    self::YuBindParam($stmt, $param);
                }
                if(!$stmt->execute()){
                    self::YuPoolPut($stmt);
                    return ["querydata" => [], "curnum" => 0, "querynum" => 0];
                }
                $result = $stmt->get_result();
                if(!$result){
                    self::YuPoolDrop($stmt);
                    return ["querydata" => [], "curnum" => 0, "querynum" => 0];
                }
                $querydata = [];
                $n = 0;
                while($row = $result->fetch_assoc()){
                    $row["_n_"] = ++$n;
                    $querydata[] = $row;
                }
                $result->free();
                self::YuPoolPut($stmt);
                $curnum = count($querydata);
                $total = $curnum;
                if($islimit && !($limitrows>0 && $curnum<$limitrows)){
                    $total = 0;
                    $countsql=self::YuCountSql($sql);
                    if($countsql!=false){
                        $countstmt=self::YuStmt($countsql,$param,$db);
                        if($countstmt){
                            if(!empty($param)){
                                self::YuBindParam($countstmt, $param);
                            }
                            if($countstmt->execute()){
                                $countstmt->bind_result($total);
                                $countstmt->fetch();
                                $countstmt->free_result();
                            }
                            self::YuPoolPut($countstmt);
                        }
                    }
                }
                return [
                    "querydata" => $querydata,
                    "curnum"    => $curnum,
                    "querynum"  => (int)$total
                ];
            }else{
                $stmt = self::YuStmt($sql,$param,$db);
                if(!$stmt){
                    return false;
                }
                if(!empty($param)){
                    self::YuBindParam($stmt, $param);
                }
                if(!$stmt->execute()){
                    self::YuPoolPut($stmt);
                    return false;
                }
                if($stmt->field_count>0){
                    $stmt->store_result();
                }
                self::YuPoolPut($stmt);
                if($yutype=="INSERT"){
                    return $db->insert_id ?: true;
                }else{
                    return true;
                }
            }
        }catch(\Throwable $e){
            $yucode=(int)$e->getCode();
            if($yucode===2006 || $yucode===2013){
                self::YuPoolClear();
            }else{
                self::YuPoolBusyClear();
            }
            error_log("[UT-SQL] ".$e->getMessage());
            if($yutype=="SELECT"){
                return ["querydata" => [], "curnum" => 0, "querynum" => 0];
            }
            return false;
        }
    }
    public static function YuCountSql($sql){
        $sql = rtrim($sql," \t\n\r;");
        $sql = preg_replace('/\s+LIMIT\s+\d+(?:\s*,\s*\d+|\s+OFFSET\s+\d+)?\s*$/i','',$sql);
        return "SELECT COUNT(*) AS total FROM ($sql) AS __count_wrapper";
    }
    private static $YuPool=array();
    private static $YuBusy=array();
    private static function YuStmt($sql,$param,$db){
        if(!is_array($param)){
            $param=array();
        }
        $types='';
        foreach($param as $value){
            if(is_int($value)){
                $types.='i';
            }elseif(is_float($value)){
                $types.='d';
            }else{
                $types.='s';
            }
        }
        $key=md5($sql."\0".$types."\0".spl_object_hash($db));
        if(isset(self::$YuPool[$key])){
            $stmt=self::$YuPool[$key];
            unset(self::$YuPool[$key]);
            if($stmt instanceof \mysqli_stmt && $stmt->errno===0){
                self::$YuBusy[spl_object_hash($stmt)]=array($key,$stmt);
                return $stmt;
            }
            if($stmt instanceof \mysqli_stmt){
                $stmt->close();
            }
        }
        $stmt=$db->prepare($sql);
        if(!$stmt){
            return false;
        }
        self::$YuBusy[spl_object_hash($stmt)]=array($key,$stmt);
        return $stmt;
    }
    private static function YuPoolPut($stmt){
        $id=spl_object_hash($stmt);
        if(!isset(self::$YuBusy[$id])){
            return;
        }
        $key=self::$YuBusy[$id][0];
        unset(self::$YuBusy[$id]);
        if(count(self::$YuPool)>=32){
            $old=array_shift(self::$YuPool);
            if($old instanceof \mysqli_stmt){
                $old->close();
            }
        }
        self::$YuPool[$key]=$stmt;
    }
    private static function YuPoolDrop($stmt){
        unset(self::$YuBusy[spl_object_hash($stmt)]);
        if($stmt instanceof \mysqli_stmt){
            $stmt->close();
        }
    }
    private static function YuPoolClear(){
        foreach(self::$YuPool as $stmt){
            if($stmt instanceof \mysqli_stmt){
                $stmt->close();
            }
        }
        foreach(self::$YuBusy as $item){
            if($item[1] instanceof \mysqli_stmt){
                $item[1]->close();
            }
        }
        self::$YuPool=array();
        self::$YuBusy=array();
    }
    private static function YuPoolBusyClear(){
        foreach(self::$YuBusy as $item){
            if($item[1] instanceof \mysqli_stmt){
                $item[1]->close();
            }
        }
        self::$YuBusy=array();
    }
    public static function YuBindParam($stmt,$params){
        if(empty($params)) return;
        $types='';
        foreach($params as $value){
            if(is_int($value)){
                $types .= 'i';
            }elseif(is_float($value)){
                $types .= 'd';
            }else{
                $types .= 's';
            }
        }
        $refs = [];
        foreach($params as $key => &$val){
            $refs[$key] = &$val;
        }
        array_unshift($refs,$types);
        call_user_func_array([$stmt,'bind_param'],$refs);
    }
    /**
     * 统计记录数目
     * @param string $sql SQL语句
     * @return int
     */
    public static function QueryNum($sql){
        $db=self::GetMysql();
        try{
            $query=$db->query($sql);
            return mysqli_num_rows($query);
        }catch(\mysqli_sql_exception $e){
            error_log("[UT-SQL] ".$e->getMessage());
            return 0;
        }
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
                if(preg_match('/^\s*(\*|[A-Za-z0-9_.`]+(\s+(as\s+)?[A-Za-z0-9_`]+)?(\s*,\s*[A-Za-z0-9_.`]+(\s+(as\s+)?[A-Za-z0-9_`]+)?)*)\s*$/i',$value)){
                    return $value;
                }
                break;
            case "column":
                if(preg_match('/^\s*(?:`[A-Za-z_][A-Za-z0-9_]*`|[A-Za-z_][A-Za-z0-9_]*)(?:\.(?:`[A-Za-z_][A-Za-z0-9_]*`|[A-Za-z_][A-Za-z0-9_]*))?\s*$/',$value)){
                    return $value;
                }
                break;
            case "where":
                if(!preg_match('/;|--|#|\/\*/',preg_replace("/'[^']*'|\"[^\"]*\"/","",$value))){
                    return $value;
                }
                break;
            case "order":
                if(preg_match('/^\s*[A-Za-z0-9_.`]+(\s+(asc|desc))?(\s*,\s*[A-Za-z0-9_.`]+(\s+(asc|desc))?)*\s*$/i',$value)){
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
}