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
 * 操作Redis
 */
class Redis{
    /**
     * 连接Redis
     */
    public static function GetRedis(){
        $config=Inc::GetConfig();
        $db=new \Redis();
        $db->connect($config["REDIS_HOST"],$config["REDIS_PORT"]);
        if($config["REDIS_PASS"]!="UT"){
            $db->auth($config["REDIS_PASS"]);
        }
        return $db;
    }
    /**
     * 判断元素是否存在
     * @param string $key 键
     * @return bool
     */
    public static function ModTable($key){
        $db=self::GetRedis();
        $res=$db->exists($key);
        if(!$res){
            return false;
        }else{
            return true;
        }
    }
    /**
     * 查询数据
     * @param string|array $key 键，单查xxx或多查array("xxx","yyy")
     * @param string $type 是否批量查询。0为单查，1为多查。
     * @return array
     */
    public static function QueryData($key,$type='0'){
        $db=self::GetRedis();
        if($type==0){
            return json_decode($db->get($key),true);
        }else{
            return json_decode($db->mget(json_encode($key)),true);
        }
    }
    /**
     * 创建数据
     * @param string $key 键
     * @param string|array $data 值
     * @param int $time 秒，0不设置过期时间，1设置过期时间为DBCACHE_TIME
     * @return bool
     */
    public static function InsertData($key,$data,$time='0'){
        $db=self::GetRedis();
        $data=is_array($data) ? json_encode($data) : $data;
        $config=Inc::GetConfig();
        if($time==0){
            $db->set($key,$data);
        }else{
            $db->set($key,$data,$config["DBCACHE_TIME"]);
        }
    }
    /**
     * 编辑数据
     * @param string $key 键
     * @param string|array $data 值
     * @param int $time 秒，0不设置过期时间，1设置过期时间为REDIS_TIME
     * @return bool
     */
    public static function UpdateData($key,$data,$time='0'){
        $db=self::GetRedis();
        $data=is_array($data) ? json_encode($data) : $data;
        $config=Inc::GetConfig();
        if(!self::ModTable($key)){
            return false;
        }else{
            if($time==0){
                $db->set($key,$data);
            }else{
                $db->set($key,$data,$config["DBCACHE_TIME"]);
            }
        }
    }
    /**
     * 删除数据
     * @param string $key 键
     */
    public static function DelData($key){
        $db=self::GetRedis();
        $db->del($key);
    }
    /**
     * 批量删除指定前缀的键
     * @param string $pix 前缀
     */
    public static function DelKeys($pix){
        $db=self::GetRedis();
        $keys=$db->keys($pix.'*');
        if(!empty($keys)){
            foreach($keys as $key){
                $db->del($key);
            }
            return true;
        }else{
            return false;
        }
    }
    /**
     * 查询所有键及键前缀模糊查询
     * @return array
     */
    public static function QueryKey($key=''){
        $db=self::GetRedis();
        if(!empty($key)){
            return $db->keys($key."*");
        }else{
            return $db->keys("*");
        }
    }
    /**
     * 查询Hash数据
     */
    public static function QueryHash($key,$field=''){
        $db=self::GetRedis();
        if(!empty($field)){
            return $db->hGet($key,$field);
        }else{
            return $db->hGetAll($key);
        }
    }
    /**
     * 创建Hash数据
     * $data array 键值对["A"=>1,"B"=>2]或["A"=>1]等同hSet
     */
    public static function InsertHash($key,$data){
        $db=self::GetRedis();
        return $db->hMSet($key, $data);
    }
    /**
     * 判断Hash中是否有某个字段
     */
    public static function ModHash($key,$field){
        $db=self::GetRedis();
        return $db->hExists($key,$field);
    }
    /**
     * 
    查询哈希表中的所有域
     */
    public static function FindHashKey($key){
        $db=self::GetRedis();
        return $db->hKeys($key);
    }
    /**
     * 创建队列任务
     * @param array $array 加入队列的数组
     */
    public static function AddQueue($array){
        $db=self::GetRedis();
        foreach($array as $k=>$v){
            $db->rpush("queue",$v);
        }
    }
    /**
     * 执行队列任务
     * @return array
     */
    public static function RunQueue(){
        $db=self::GetRedis(); 
        $value=$db->lpop('queue');
        if($value){
            echo$value;
        }else{
            echo"Queue Complete";
        }
    }
    /**
     * 清空当前数据库
     * @return bool
     */
    public static function Clear(){
        $db=self::GetRedis();
        return $db->flushdb();
    }
}