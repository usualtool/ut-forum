<?php
namespace usualtool\Lib;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
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
* 以PDO方法操作数据库
*/   
class Pdo{
    /**
     * PDO连接数据库
     */    
    public static function GetPdo(){
    static $db=null;
    if($db instanceof \PDO){
        return $db;
    }
    $config=Inc::GetConfig();
    $dsn=str_replace("__","=",$config['PDO_DSN']);
    try {
       $db=new \PDO($dsn,$config['PDO_USER'],$config['PDO_PASS']);
    }catch(\PDOException $e){
      error_log("[UT-SQL] ".$e->getMessage());
      if(PHP_SAPI==="cli"){
          echo $e->getMessage()."\r\n";
          exit();
      }
      http_response_code(503);
      header('Content-Type: text/html; charset=utf-8');
      echo'<pre style="padding:24px;font:14px/1.8 monospace">数据库连接失败，请检查 .ut.config 的 PDO 配置</pre>';
      exit;
    }
    try{
        if(strpos($dsn,'mysql:')===0 && stripos($dsn,'charset=')===false){
            $db->query("set names ".$config['MYSQL_CHARSET']);
        }elseif(strpos($dsn,'pgsql:')===0){
            $db->query("set names 'UTF8'");
        }elseif(strpos($dsn,'sqlite:')===0){
            $db->exec("pragma journal_mode=WAL");
            $db->exec("pragma synchronous=NORMAL");
            $db->exec("pragma busy_timeout=5000");
        }
    }catch(\PDOException $e){
        error_log("[UT-SQL] ".$e->getMessage());
    }
    $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE,\PDO::FETCH_ASSOC);
    return $db;
    }
    /**
     * 判断表是否存在
     * @param string $table
     * @return bool
     */
    public static function ModTable($table){
    static $mods=array();
    if(!preg_match('/^[A-Za-z0-9_]+$/',(string)$table)){
        return false;
    }
    if(isset($mods[$table])){
        return $mods[$table];
    }
    $db=Pdo::GetPdo();
    $config=Inc::GetConfig();
    $sql=strpos($config['PDO_DSN'],'sqlsrv')!==false ? "SELECT TOP 1 1 FROM ".$table : "SELECT 1 FROM ".$table." LIMIT 1";
    try{
        $query=$db->prepare($sql);
        $query->execute();
        $mods[$table]=true;
    }catch(\PDOException $e){
        error_log("[UT-SQL] ".$e->getMessage());
        $mods[$table]=false;
    }
    return $mods[$table];
    }
    /**
    * 执行语句
     * @param string $sql SQL语句
     * @return bool
     */    
    public static function RunSql($sql){
    $db=Pdo::GetPdo();
    $query=false;
    try{
        $query=$db->exec($sql);
    }catch(\PDOException $e){
        error_log("[UT-SQL] ".$e->getMessage());
        $query=false;
    }
    if($query===false){
        return false;
    }else{
        return true;
    }
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
      if(!preg_match('/^[A-Za-z0-9_]+(\.[A-Za-z0-9_]+)?$/',(string)$table)){
        return array("querydata"=>array(),"curnum"=>0,"querynum"=>0);
      }
      $field=empty($field) ? "*" : $field;
      $field=self::CheckPart("field",$field);
      if($field===false){
        $field="*";
      }
      if($where!=="" && self::CheckPart("where",$where)===false){
        return array("querydata"=>array(),"curnum"=>0,"querynum"=>0);
      }
      if($order!==""){
        $order=self::CheckPart("order",$order);
        if($order===false){
          $order="";
        }
      }
      $config=Inc::GetConfig();
      $limit=(string)$limit==="" ? "" : $limit;
      if($limit!=="" && self::CheckPart("limit",$limit)===false){
        return array("querydata"=>array(),"curnum"=>0,"querynum"=>0);
      }
      $where=empty($where) ? "" : "where ".$where;
      $order=empty($order) ? "" : "order by ".$order;
      if(strpos($config['PDO_DSN'],'sqlsrv')!==false){
        $sql="select ".($limit==="" ? "" : "top ".$limit." ").$field." from ".$table." ".$where." ".$order;
      }else{
        $sql="select ".$field." from ".$table." ".$where." ".$order." ".($limit==="" ? "" : "limit ".$limit);
      }
      if(is_array($param) && !empty($param) && self::YuSlots($sql)==count($param)){
        return self::RunYu($sql,$param);
      }
      $db=Pdo::GetPdo();
      try{
        $stmt=$db->prepare($sql);
        $stmt->execute();
        $rows=$stmt->fetchAll(\PDO::FETCH_ASSOC);
      }catch(\PDOException $e){
        error_log("[UT-SQL] ".$e->getMessage());
        return array("querydata"=>array(),"curnum"=>0,"querynum"=>0);
      }
      $array=array();
      $n=0;
      foreach($rows as $row){
        $row["_n_"]=++$n;
        $array[]=$row;
      }
      $curnum=count($array);
      $querynum=$limit==="" ? $curnum : Pdo::QueryNum("select count(*) from ".$table." ".$where);
      $data=array("querydata"=>$array,"curnum"=>$curnum,"querynum"=>$querynum);
      return $data;
    }
    /**
     * 创建数据
     * @param string $table 表名
     * @param string $data 字段/值数组
     * @param array $param 预处理值数组
     * @return bool 
     */
    public static function InsertData($table,$data,$param=array()){
      if(!preg_match('/^[A-Za-z0-9_]+(\.[A-Za-z0-9_]+)?$/',(string)$table)){
          return false;
      }
      $db=Pdo::GetPdo();
      if(!is_array($data) || empty($data)){
          return false;
      }
      foreach(array_keys($data) as $k){
          if(self::CheckPart("column",(string)$k)===false){
              return false;
          }
      }
      $values=array_values($data);
      $slot=0;
      foreach($values as $v){
          if($v==="?"){ $slot++; }
      }
      $bind=is_array($param) && !empty($param) && $slot==count($param);
      if(is_array($param) && !empty($param) && !$bind){
          return false;
      }
      $args=array();
      $pi=0;
      foreach($values as $v){
          if($bind && $v==="?"){
              $args[]=$param[$pi++];
          }elseif(is_null($v)){
              $args[]=null;
          }elseif(is_bool($v)){
              $args[]=$v ? 1 : 0;
          }else{
              $args[]=(string)$v;
          }
      }
      $sql="insert into ".$table." (".implode(',',array_keys($data)).") values (".implode(",",array_fill(0,count($values),"?")).")";
      try{
          $stmt=$db->prepare($sql);
          $stmt->execute($args);
      }catch(\PDOException $e){
          error_log("[UT-SQL] ".$e->getMessage());
          return false;
      }
      $query=$db->lastInsertId();
      return $query ? (int)$query : true;
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
        if(!preg_match('/^[A-Za-z0-9_]+(\.[A-Za-z0-9_]+)?$/',(string)$table)){
            return false;
        }
        if(self::CheckPart("where",(string)$where)===false){
            return false;
        }
        $db=Pdo::GetPdo();
        $slot=self::YuSlots((string)$where);
        if(is_array($data)){
            foreach(array_keys($data) as $k){
                if(self::CheckPart("column",(string)$k)===false){
                    return false;
                }
            }
            foreach(array_values($data) as $v){
                if($v==="?"){ $slot++; }
            }
        }
        $bind=is_array($param) && !empty($param) && $slot==count($param);
        if(is_array($param) && !empty($param) && !$bind){
            error_log("[UT-SQL] param mismatch: ".$slot." slots / ".count($param)." params");
            return false;
        }
        $updatestr='';
        $args=array();
        $pi=0;
        if(!empty($data)){
            foreach($data as $k=>$v){
                if($bind && $v==="?"){
                    $updatestr.=$k."=?,";
                    $args[]=$param[$pi++];
                }elseif(is_null($v)){
                    $updatestr.=$k."=NULL,";
                }elseif(is_bool($v)){
                    $updatestr.=$k."=".($v ? 1 : 0).",";
			    }elseif(is_string($v) && preg_match('/^[A-Za-z_][A-Za-z0-9_]*\+\d+$/',$v)){
			        $updatestr.=$k."=".$v.",";
			    }else{
                    $updatestr.=$k."=?,";
                    $args[]=(string)$v;
		        }
            }
            $updatestr=rtrim($updatestr,',');
        }
        if($bind){
            while($pi<count($param)){
                $args[]=$param[$pi++];
            }
        }
        $sql="update ".$table." set ".$updatestr." where ".$where;
        try{
            $stmt=$db->prepare($sql);
            $stmt->execute($args);
        }catch(\PDOException $e){
            error_log("[UT-SQL] ".$e->getMessage());
            return false;
        }
        return true;
    }
    /**
     * 删除数据
     * @param string $table 表名
     * @param string $where 条件
     * @param array $param 预处理值数组
     * @return bool
     */
    public static function DelData($table,$where,$param=array()){
        if(!preg_match('/^[A-Za-z0-9_]+(\.[A-Za-z0-9_]+)?$/',(string)$table)){
            return false;
        }
        if(self::CheckPart("where",(string)$where)===false){
            return false;
        }
        $db=Pdo::GetPdo();
        $args=array();
        if(is_array($param) && !empty($param)){
            if(self::YuSlots((string)$where)!=count($param)){
                error_log("[UT-SQL] param mismatch: ".self::YuSlots((string)$where)." slots / ".count($param)." params");
                return false;
            }
            $args=array_values($param);
        }
        $sql="delete from ".$table." where ".$where;
        try{
            $stmt=$db->prepare($sql);
            $stmt->execute($args);
        }catch(\PDOException $e){
            error_log("[UT-SQL] ".$e->getMessage());
            return false;
        }
        return true;
    }
    /**
     * 执行预处理
     * @param string $sql SQL语句
     * @param array $param 预处理值数组
     * @return array|bool|int
     */
    public static function RunYu($sql,$param=[]){
        $db=Pdo::GetPdo();
        $trimmed=ltrim($sql);
        $yutype=preg_match('/^([A-Za-z]+)/',$trimmed,$m) ? strtoupper($m[1]) : "";
        if($yutype=="WITH"){
            $yutype=preg_match('/\)\s*(insert|update|delete|replace)\b/i',$trimmed) ? "INSERT" : "SELECT";
        }
        $empty=array("querydata"=>array(),"curnum"=>0,"querynum"=>0);
        $slot=self::YuSlots($sql);
        if($slot!=count((array)$param)){
            error_log("[UT-SQL] param mismatch: ".$slot." slots / ".count((array)$param)." params");
            return $yutype=="SELECT" ? $empty : false;
        }
        if($yutype=="SELECT"){
            $islimit = (bool) preg_match('/\bLIMIT\b/i',$sql);
            $total = 0;
            if($islimit){
                $countsql=Pdo::YuCountSql($sql);
                if($countsql!=false){
                    try{
                        $countstmt=$db->prepare($countsql);
                        $countstmt->execute($param);
                        $total=(int) $countstmt->fetchColumn();
                    }catch(\PDOException $e){
                        error_log("[UT-SQL] ".$e->getMessage());
                        $total=0;
                    }
                }
            }
            try{
                $stmt=$db->prepare($sql);
                $stmt->execute($param);
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }catch(\PDOException $e){
                error_log("[UT-SQL] ".$e->getMessage());
                return $empty;
            }
            $querydata = [];
            $n = 0;
            foreach($result as $row){
                $row["_n_"] = ++$n;
                $querydata[] = $row;
            }
            $curnum = count($querydata);
            if(!$islimit){
                $total = $curnum;
            }
            return [
                "querydata" => $querydata,
                "curnum"    => $curnum,
                "querynum"  => $total
            ];
        }else{
            try{
                $stmt=$db->prepare($sql);
                $stmt->execute($param);
            }catch(\PDOException $e){
                error_log("[UT-SQL] ".$e->getMessage());
                return false;
            }
            if($yutype=="INSERT"){
                $insertid=$db->lastInsertId();
                return $insertid ? (int)$insertid : true;
            }else{
                return true;
            }
        }
    }
    /**
     * 统计占位符个数，跳过字符串、标识符与注释内的问号
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
    public static function YuCountSql($sql){
        $sql = rtrim($sql, " \t\n\r;");
        $sql = preg_replace('/\s+LIMIT\s+\d+(?:\s*,\s*\d+|\s+OFFSET\s+\d+)?\s*$/i','',$sql);
        $sql = preg_replace('/^\s*SELECT\s+TOP\s+\d+/i','SELECT',$sql);
        $sql = preg_replace('/\s+OFFSET\s+\d+\s+ROWS\s+FETCH\s+NEXT\s+\d+\s+ROWS\s+ONLY\s*$/i','',$sql);
        return "SELECT COUNT(*) AS total FROM ($sql) AS __count_wrapper";
    }
    /**
     * 统计记录数目
     * @param string $sql SQL语句
     * @return int
     */
    public static function QueryNum($sql){
        $db=Pdo::GetPdo();
        try{
            $query=$db->prepare($sql);
            $query->execute();
            $querynum=$query->fetchColumn();
        }catch(\PDOException $e){
            return 0;
        }
        return $querynum;
    }
    public static function CheckPart($part,$value){
        return Data::CheckPart($part,$value,'pdo');
    }
}