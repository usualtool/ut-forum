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
        $config=Inc::GetConfig();
        return $config["DBTYPE"];
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
     * @param string $table 被查询表名
     * @param string $field 查询字段，多个字段以‘,’分割
     * @param string $where 查询条件
     * @param string $order 排序方式，例：id desc/id asc
     * @param string|int $limit 数据显示数目，例：0,5/1
     * @param string $lang 是否开启语言识别，默认0关闭，当需要开启时，该参数填写>0的数字，自动获取全局的语言参数，也可以直接填写语言参数zh/en/ja等
     * @param string $cache 是否开启缓存，默认0关闭，需要开启时，该参数填写key名称
     * @return array 返回数组，例：array("querydata"=>array(),"curnum"=>0,"querynum"=>0)
     */
    public static function QueryData($table,$field='',$where='',$order='',$limit='',$lang='0',$cache='0'){
        if($cache==0){
            if(self::GetDb()=="pdo"){
                $data=Pdo::QueryData($table,$field,$where,$order,$limit,$lang);
            }elseif(self::GetDb()=="mysql"){
                $data=Mysql::QueryData($table,$field,$where,$order,$limit,$lang);
            }elseif(self::GetDb()=="mssql"){
                $data=Mssql::QueryData($table,$field,$where,$order,$limit,$lang);
            }elseif(self::GetDb()=="pgsql"){
                $data=Pgsql::QueryData($table,$field,$where,$order,$limit,$lang);
            }elseif(self::GetDb()=="sqlite"){
                $data=Sqlite::QueryData($table,$field,$where,$order,$limit,$lang);
            }else{
                $data=array();
            }
            return $data;
        }else{
            self::GetCache($table,$field,$where,$order,$limit,$lang,$cache);
        }
    }
    /**
     * 执行SQL并返回数据集
     * @param string $sql SQL语句/命令
     * @return bool
     */
    public static function JoinQuery($sql){
        if(self::GetDb()=="pdo"){
            return Pdo::JoinQuery($sql);
        }elseif(self::GetDb()=="mysql"){
            return Mysql::JoinQuery($sql);
        }elseif(self::GetDb()=="mssql"){
            return Mssql::JoinQuery($sql);
        }elseif(self::GetDb()=="pgsql"){
            return Pgsql::JoinQuery($sql);
        }elseif(self::GetDb()=="sqlite"){
            return Sqlite::JoinQuery($sql);
        }else{
            return false;
        }
    }
    /**
     * 创建数据
     * @param string $table 表名
     * @param array $data 字段及值的数组，例：array("字段1"=>"值1","字段2"=>"值2")
     * @return bool 当结果为真时返回最新添加的记录id
     */
    public static function InsertData($table,$data){
        if(self::GetDb()=="pdo"){
            return Pdo::InsertData($table,$data);
        }elseif(self::GetDb()=="mysql"){
            return Mysql::InsertData($table,$data);
        }elseif(self::GetDb()=="mssql"){
            return Mssql::InsertData($table,$data);
        }elseif(self::GetDb()=="pgsql"){
            return Pgsql::InsertData($table,$data);
        }elseif(self::GetDb()=="sqlite"){
            return Sqlite::InsertData($table,$data);
        }else{
            return false;
        }
    }
    /**
     * 更新数据
     * @param string $table 表名
     * @param array $data 字段及值的数组，例：array("字段1"=>"值1","字段2"=>"值2")
     * @param string $where 条件
     * @return bool
     */
    public static function UpdateData($table,$data,$where){
        if(self::GetDb()=="pdo"){
            return Pdo::UpdateData($table,$data,$where);
        }elseif(self::GetDb()=="mysql"){
            return Mysql::UpdateData($table,$data,$where);
        }elseif(self::GetDb()=="mssql"){
            return Mssql::UpdateData($table,$data,$where);
        }elseif(self::GetDb()=="pgsql"){
            return Pgsql::UpdateData($table,$data,$where);
        }elseif(self::GetDb()=="sqlite"){
            return Sqlite::UpdateData($table,$data,$where);
        }else{
            return false;
        }
    }
    /**
     * 删除数据
     * @param string $table 表名
     * @param string $where 条件
     * @return bool
     */
    public static function DelData($table,$where){
        if(self::GetDb()=="pdo"){
            return Pdo::DelData($table,$where);
        }elseif(self::GetDb()=="mysql"){
            return Mysql::DelData($table,$where);
        }elseif(self::GetDb()=="mssql"){
            return Mssql::DelData($table,$where);
        }elseif(self::GetDb()=="pgsql"){
            return Pgsql::DelData($table,$where);
        }elseif(self::GetDb()=="sqlite"){
            return Sqlite::DelData($table,$where);
        }else{
            return false;
        }
    }
    /**
     * 执行预处理
     * @param string $table 表名
     * @param string $where 条件
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
     * @param array $where 条件
	 * @param string autokey 自动编号字段
     * @return bool 当结果为真时返回最新添加的记录id
     */
    public static function CopyData($table,$where,$autokey='id'){
        if(self::GetDb()=="mysql"){
            return Mysql::CopyData($table,$where,$autokey);
        }else{
            return false;
        }
    }
    /**
     * 获取数据标签
     * @param string $table 表名
     * @param string $field 标签字段，只能为1个
     * @param string $where 条件
     * @param string $order 排序方式
     * @param string $lang 是否自动开启语言，默认0关闭
     * @param string $cache 是否开启redis缓存，默认0关闭，需要开启时，该参数填写key名称
     * @return array 返回数组，例：array('tags'=>$taglist)
     */
    public static function TagData($table,$field='',$where='',$order='',$lang='0'){
        if(self::GetDb()=="mysql"){
            return Mysql::TagData($table,$field,$where,$order,$lang);
        }elseif(self::GetDb()=="mssql"){
            return Mssql::TagData($table,$field,$where,$order,$lang);
        }elseif(self::GetDb()=="pgsql"){
            return Pgsql::TagData($table,$field,$where,$order,$lang);
        }elseif(self::GetDb()=="sqlite"){
            return Sqlite::TagData($table,$field,$where,$order,$lang);
        }else{
            return array();
        }
    }
    /**
     * 获取数据首图
     * @param string $table 表名
     * @param string $field 检索字段，只能为1个
     * @param string $where 条件
     * @param string $cache 是否开启redis缓存，默认0关闭，需要开启时，该参数填写key名称
     * @return array 返回数组，在其数组中返回指定字段的第一张图片imageurl
     */
    public static function FigureData($table,$field,$where='',$limit=''){
        if(self::GetDb()=="mysql"){
            return Mysql::FigureData($table,$field,$where,$limit);
        }elseif(self::GetDb()=="mssql"){
            return Mssql::FigureData($table,$field,$where,$limit);
        }elseif(self::GetDb()=="pgsql"){
            return Pgsql::FigureData($table,$field,$where,$limit);
        }elseif(self::GetDb()=="sqlite"){
            return Sqlite::FigureData($table,$field,$where,$limit);
        }else{
            return array();
        }
    }
    /**
     * 搜索数据
     * @param string $keyword 关键词
     * @return array 返回数组
     */
    public static function SearchData($keyword){
        if(self::GetDb()=="mysql"){
            return Mysql::SearchData($keyword);
        }else{
            return array();
        }	
    }
    /**
     * 获取及更新缓存
     * @param string $table 表
     * @param string $field 字段
     * @param string $where 条件
     * @param string $order 排序
     * @param string $limit 数量
     * @param string $lang 语言
     * @param string $cache 键或元素
     * @return array
     */
    public static function GetCache($table,$field,$where,$order,$limit,$lang,$cache){
        $config=Inc::GetConfig();
        $dbcache=$config["DBCACHE"];
        if($dbcache=="redis"){
            if(Redis::ModTable($cache)){
                return Redis::QueryData($cache);
            }else{
                $data=self::QueryData($table,$field,$where,$order,$limit,$lang,0);
                Redis::InsertData($cache,$data,1);
                return $data;
            }
        }elseif($dbcache=="mongo"){
            if(Mongo::ModTable($cache)){
                return Mongo::QueryData($cache);
            }else{
                $data=self::QueryData($table,$field,$where,$order,$limit,$lang,0);
                Mongo::InsertData($cache,$data);
                return $data;
            }
        }elseif($dbcache=="memcache"){
            if(Memcache::ModTable($cache)){
                return Memcache::QueryData($cache);
            }else{
                $data=self::QueryData($table,$field,$where,$order,$limit,$lang,0);
                Memcache::InsertData($cache,$data,1);
                return $data;
            }
        }else{
            return array();
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
}