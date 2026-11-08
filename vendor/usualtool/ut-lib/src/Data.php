<?php
namespace usualtool\Lib;
use usualtool\Lib\Inc;
use usualtool\Lib\Pdo;
use usualtool\Lib\Mysql;
use usualtool\Lib\Mssql;
use usualtool\Lib\Pgsql;
use usualtool\Lib\Sqlite;
use usualtool\Lib\Mongo;
use usualtool\Lib\Redis;
use usualtool\Lib\Memcache;
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
 * 统一操作数据
 */
class Data{
    /**
     * 获取主数据库
     */
    public static function GetDb(){
        static $dbtype=null;
        if($dbtype===null){
            $config=Inc::GetConfig();
            $dbtype=$config["DBTYPE"];
        }
        return $dbtype;
    }
    /**
     * 连接数据库
     */
    public static function GetDatabase(){
        if(self::GetDb()=="pdo"){
            return Pdo::GetPdo();
        }elseif(self::GetDb()=="mysql"){
            return Mysql::GetMysql();
        }elseif(self::GetDb()=="mssql"){
            return Mssql::GetMssql();
        }elseif(self::GetDb()=="pgsql"){
            return Pgsql::GetPgsql();
        }elseif(self::GetDb()=="sqlite"){
            return Sqlite::GetSqlite();
        }else{
            return false;
        }
    }
    /**
     * 判断表是否存在
     * @param string $table
     * @return bool
     */
    public static function ModTable($table){
        if(self::GetDb()=="pdo"){
            return Pdo::ModTable($table);
        }elseif(self::GetDb()=="mysql"){
            return Mysql::ModTable($table);
        }elseif(self::GetDb()=="mssql"){
            return Mssql::ModTable($table);
        }elseif(self::GetDb()=="pgsql"){
            return Pgsql::ModTable($table);
        }elseif(self::GetDb()=="sqlite"){
            return Sqlite::ModTable($table);
        }else{
            return false;
        }
    }
    /**
     * 执行SQL或命令
     * @param string $sql SQL语句/命令
     * @return bool
     */
    public static function RunSql($sql){
        if(self::GetDb()=="pdo"){
            return Pdo::RunSql($sql);
        }elseif(self::GetDb()=="mysql"){
            return Mysql::RunSql($sql);
        }elseif(self::GetDb()=="mssql"){
            return Mssql::RunSql($sql);
        }elseif(self::GetDb()=="pgsql"){
            return Pgsql::RunSql($sql);
        }elseif(self::GetDb()=="sqlite"){
            return Sqlite::RunSql($sql);
        }else{
            return false;
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
        if(self::GetDb()=="pdo"){
            $data=Pdo::QueryData($table,$field,$where,$order,$limit,$param);
        }elseif(self::GetDb()=="mysql"){
            $data=Mysql::QueryData($table,$field,$where,$order,$limit,$param);
        }elseif(self::GetDb()=="mssql"){
            $data=Mssql::QueryData($table,$field,$where,$order,$limit,$param);
        }elseif(self::GetDb()=="pgsql"){
            $data=Pgsql::QueryData($table,$field,$where,$order,$limit,$param);
        }elseif(self::GetDb()=="sqlite"){
            $data=Sqlite::QueryData($table,$field,$where,$order,$limit,$param);
        }else{
            $data=array();
        }
        return $data;
    }
    /**
     * 创建数据
     * @param string $table 表名
     * @param array $data 字段/值数组
     * @param array $param 预处理值数组
     * @return bool
     */
    public static function InsertData($table,$data,$param=array()){
        if(self::GetDb()=="pdo"){
            return Pdo::InsertData($table,$data,$param);
        }elseif(self::GetDb()=="mysql"){
            return Mysql::InsertData($table,$data,$param);
        }elseif(self::GetDb()=="mssql"){
            return Mssql::InsertData($table,$data,$param);
        }elseif(self::GetDb()=="pgsql"){
            return Pgsql::InsertData($table,$data,$param);
        }elseif(self::GetDb()=="sqlite"){
            return Sqlite::InsertData($table,$data,$param);
        }else{
            return false;
        }
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
        if(self::GetDb()=="pdo"){
            return Pdo::UpdateData($table,$data,$where,$param);
        }elseif(self::GetDb()=="mysql"){
            return Mysql::UpdateData($table,$data,$where,$param);
        }elseif(self::GetDb()=="mssql"){
            return Mssql::UpdateData($table,$data,$where,$param);
        }elseif(self::GetDb()=="pgsql"){
            return Pgsql::UpdateData($table,$data,$where,$param);
        }elseif(self::GetDb()=="sqlite"){
            return Sqlite::UpdateData($table,$data,$where,$param);
        }else{
            return false;
        }
    }
    /**
     * 删除数据
     * @param string $table 表名
     * @param string $where 条件
     * @param array $param 预处理值数组
     * @return bool
     */
    public static function DelData($table,$where,$param=array()){
        if(self::GetDb()=="pdo"){
            return Pdo::DelData($table,$where,$param);
        }elseif(self::GetDb()=="mysql"){
            return Mysql::DelData($table,$where,$param);
        }elseif(self::GetDb()=="mssql"){
            return Mssql::DelData($table,$where,$param);
        }elseif(self::GetDb()=="pgsql"){
            return Pgsql::DelData($table,$where,$param);
        }elseif(self::GetDb()=="sqlite"){
            return Sqlite::DelData($table,$where,$param);
        }else{
            return false;
        }
    }
    /**
     * 执行预处理
     * @param string $sql SQL语句
     * @param array $param 预处理值数组
     * @return bool
     */
    public static function RunYu($sql,$param=[]){
        if(self::GetDb()=="pdo"){
            return Pdo::RunYu($sql,$param);
        }elseif(self::GetDb()=="mysql"){
            return Mysql::RunYu($sql,$param);
        }elseif(self::GetDb()=="mssql"){
            return Mssql::RunYu($sql,$param);
        }elseif(self::GetDb()=="pgsql"){
            return Pgsql::RunYu($sql,$param);
        }elseif(self::GetDb()=="sqlite"){
            return Sqlite::RunYu($sql,$param);
        }else{
            return false;
        }
    }
    /**
     * 复制数据
     * @param string $table 表名
     * @param string $where 条件
	 * @param array $param 预处理值数组
     * @return bool
     */
    public static function CopyData($table,$where,$param=array()){
        if(self::GetDb()=="mysql"){
            return Mysql::CopyData($table,$where,$param);
        }else{
            return false;
        }
    }
    /**
     * 获取记录数目
     * @param string $sql SQL语句
     * @return int 
     */
    public static function QueryNum($sql){
            if(self::GetDb()=="pdo"){
                $data=Pdo::QueryNum($sql);
            }elseif(self::GetDb()=="mysql"){
                $data=Mysql::QueryNum($sql);
            }elseif(self::GetDb()=="mssql"){
                $data=Mssql::QueryNum($sql);
            }elseif(self::GetDb()=="pgsql"){
                $data=Pgsql::QueryNum($sql);
            }elseif(self::GetDb()=="sqlite"){
                $data=Sqlite::QueryNum($sql);
            }else{
                $data=array();
            }
            return $data;
    }
    /**
     * 获取及更新缓存
     * @param string $table 表
     * @param string $field 字段
     * @param string $where 条件
     * @param string $order 排序
     * @param string $limit 数目
	 * @param array $param 预处理值数组
     * @param string $cache 键或元素
     * @return array
     */
    public static function GetCache($table,$field,$where,$order,$limit,$param,$cache){
        $config=Inc::GetConfig();
        $dbcache=$config["DBCACHE"];
        if($dbcache=="redis"){
            if(Redis::ModTable($cache)){
                return Redis::QueryData($cache);
            }else{
                $data=self::QueryData($table,$field,$where,$order,$limit,$param);
                Redis::InsertData($cache,$data,1);
                return $data;
            }
        }elseif($dbcache=="mongo"){
            if(Mongo::ModTable($cache)){
                return Mongo::QueryData($cache);
            }else{
                $data=self::QueryData($table,$field,$where,$order,$limit,$param);
                Mongo::InsertData($cache,$data);
                return $data;
            }
        }elseif($dbcache=="memcache"){
            if(Memcache::ModTable($cache)){
                return Memcache::QueryData($cache);
            }else{
                $data=self::QueryData($table,$field,$where,$order,$limit,$param);
                Memcache::InsertData($cache,$data,1);
                return $data;
            }
        }else{
            return array();
        }
    }
}