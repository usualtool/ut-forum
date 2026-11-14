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
    private static $db=null;
    private static $mark="@UT@";
    /**
     * 连接Redis
     */
    public static function GetRedis(){
        if(self::$db!==null){
            return self::$db;
        }
        $config=Inc::GetConfig();
        $db=new \Redis();
        $db->connect($config["REDIS_HOST"],$config["REDIS_PORT"]);
        if($config["REDIS_PASS"]!="UT"){
            $db->auth($config["REDIS_PASS"]);
        }
        self::$db=$db;
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
            return self::Decode($db->get($key));
        }else{
            $raw=$db->mget(is_array($key) ? array_values($key) : array($key));
            if(!is_array($raw)){
                return array();
            }
            $data=array();
            foreach($raw as $value){
                $data[]=self::Decode($value);
            }
            return $data;
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
        $value=self::Encode($data);
        $ttl=$time==0 ? 0 : self::Ttl();
        return $ttl>0 ? $db->set($key,$value,$ttl) : $db->set($key,$value);
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
        if(!$db->exists($key)){
            return false;
        }
        $value=self::Encode($data);
        $ttl=$time==0 ? 0 : self::Ttl();
        return $ttl>0 ? $db->set($key,$value,$ttl) : $db->set($key,$value);
    }
    /**
     * 删除数据
     * @param string $key 键
     */
    public static function DelData($key){
        $db=self::GetRedis();
        return $db->del($key)>0;
    }
    /**
     * 批量删除指定前缀的键
     * @param string $pix 前缀
     */
    public static function DelKeys($pix){
        $db=self::GetRedis();
        $keys=self::ScanKeys($pix.'*');
        if(empty($keys)){
            return false;
        }
        foreach(array_chunk($keys,500) as $chunk){
            $db->del($chunk);
        }
        return true;
    }
    /**
     * 查询所有键及键前缀模糊查询
     * @return array
     */
    public static function QueryKey($key=''){
        return self::ScanKeys(empty($key) ? "*" : $key."*");
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
    public static function Close(){
        if(self::$db!==null){
            try{
                self::$db->close();
            }catch(\Throwable $e){
            }
            self::$db=null;
        }
    }
    private static function Ttl(){
        $config=Inc::GetConfig();
        $ttl=isset($config["DBCACHE_TIME"]) ? (int)$config["DBCACHE_TIME"] : 0;
        return $ttl>0 ? $ttl : 0;
    }
    private static function Encode($data){
        return self::$mark.json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
    private static function Decode($raw){
        if(is_array($raw)){
            return $raw;
        }
        if($raw===false || $raw===null){
            return null;
        }
        $raw=(string)$raw;
        $len=strlen(self::$mark);
        if(strncmp($raw,self::$mark,$len)===0){
            return json_decode(substr($raw,$len),true);
        }
        return json_decode($raw,true);
    }
    private static function ScanKeys($pattern){
        $db=self::GetRedis();
        if(!method_exists($db,'scan')){
            return $db->keys($pattern);
        }
        $keys=array();
        $it=null;
        $guard=0;
        while(true){
            if($guard++>100000){
                break;
            }
            $res=$db->scan($it,$pattern,500);
            if($res===false){
                break;
            }
            if(is_array($res) && !empty($res)){
                foreach($res as $key){
                    $keys[]=$key;
                }
            }
            if(empty($it)){
                break;
            }
        }
        return $keys;
    }
}