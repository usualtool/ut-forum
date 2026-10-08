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
 * 爬虫工具
 */
class Spider{
    protected $http_data = array();
    protected $agent;
    protected $cookies;
    protected $referer;
    protected $ip;
    protected $header = array();
    protected $_option = array();
    protected $_post_data = array();
    //多列队任务进程数，0表示不限制
    protected $multi_exec_num = 100;
    const ERROR_HOST = 'NULL';
    const ERROR_GET = 'NULL';
    const ERROR_POST = 'NULL';
    function __construct(){}
    public function SetAgent($agent){
        $this->agent = $agent;
        return $this;
    }
    public function SetCookies($cookies){
        $this->cookies = $cookies;
        return $this;
    }
    public function SetReferer($referer){
        $this->referer = $referer;
        return $this;
    }
    public function SetIp($ip){
        $this->ip = $ip;
        return $this;
    }
    public function SetOption($key, $value){
        if ( $key===CURLOPT_HTTPHEADER ){
            $this->header = array_merge($this->header,$value);
        }else{
            $this->_option[$key] = $value;
        }
        return $this;
    }
    public function SetMultiMaxNum($num=0){
        $this->multi_exec_num = (int)$num;
        return $this;
    }
    public function Post($url, $vars, $timeout = 60){
        $this->SetOption(CURLOPT_HTTPHEADER,array('Accept-Language:zh-CN'));
        $this->SetOption(CURLOPT_POST,true);
        if(is_array($url)){
            $myvars = array();
            foreach ($url as $k=>$url){
                if (isset($vars[$k])){
                    if (is_array($vars[$k])){
                        $myvars[$url] = http_build_query($vars[$k]);
                    }else{
                        $myvars[$url] = $vars[$k];
                    }
                }
            }
        }else{
        $myvars = array($url=>$vars);
        }
        $this->_post_data = $myvars;
        return $this->Get($url,$timeout);
    }
    public function Get($url, $timeout = 30){
        if(is_array($url)){
            $getone = false;
            $urls = $url;
        }else{
            $getone = true;
            $urls = array($url);
        }
        $data = $this->RequestUrls($urls, $timeout);
        $this->ClearSet();
        if($getone){
            $this->http_data = $this->http_data[$url];
            $encode = mb_detect_encoding($data[$url], array('GB2312','GBK','UTF-8'));
            if($encode=="GB2312"){$datas = iconv("GBK","UTF-8",$data[$url]);}
            else if($encode=="GBK"){$datas = iconv("GBK","UTF-8",$data[$url]);}
            else if($encode=="EUC-CN"){$datas = iconv("GBK","UTF-8",$data[$url]);}
            else{$datas =$data[$url];}
            return $datas;
        }else{
            return $data;
        }
    }
    public function _create($url,$timeout){
        if(false===strpos($url, '://')){
            preg_match('#^(http(?:s)?\://[^/]+/)#',($_SERVER["SCRIPT_URI"] ?? ''),$m);
            $the_url = $m[1].ltrim($url,'/');
        }else{
            $the_url = $url;
        } 
        if ($this->ip){
            if ( preg_match('#^(http(?:s)?)\://([^/\:]+)(\:[0-9]+)?/#', $the_url.'/',$m) ){
                $this->header[] = 'Host: '.$m[2];
                $the_url = $m[1].'://'.$this->ip.$m[3].'/'.substr($the_url,strlen($m[0]));
            }
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $the_url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        if(preg_match('#^https://#i', $the_url)){
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        if($this->cookies){
            curl_setopt($ch, CURLOPT_COOKIE, http_build_query($this->cookies, '', ';'));
        }
        if($this->referer){
            curl_setopt($ch, CURLOPT_REFERER, $this->referer);
        }
        if($this->agent){
            curl_setopt($ch, CURLOPT_USERAGENT, $this->agent);
        }elseif(isset($_SERVER['HTTP_USER_AGENT'])){
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        }
        foreach($this->_option as $k=>$v){
            curl_setopt($ch, $k, $v);
        }
        if($this->header){
            $header = array();
            foreach ($this->header as $item){
            if (preg_match('#(^[^:]*):.*$#', $item,$m)){
                $header[$m[1]] = $item;
            }
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_values($header));
        }
        if(isset($this->_post_data[$the_url])){
            curl_setopt($ch , CURLOPT_POSTFIELDS , $this->_post_data[$the_url]);
        }
        return $ch;
    }
    protected function RequestUrls($urls, $timeout = 10){
        $urls = array_unique($urls);
        if(!$urls)return array();
        $mh = curl_multi_init();
        $listener_list = array();
        $result = array();
        $list_num = 0;
        $multi_list = array();
        foreach ( $urls as $url ){
        $current = $this->_create($url, $timeout);
        if($this->multi_exec_num>0 && $list_num>=$this->multi_exec_num ){
            $multi_list[] = $url;
        }else{
            curl_multi_add_handle($mh, $current);
            $listener_list[$url] = $current;
            $list_num++;
        }
        $result[$url] = null;
        $this->http_data[$url] = null;
        }
        unset($current);
        $running = null;
        $done_num = 0; 
        do{
            while(($execrun = curl_multi_exec($mh,$running)) == CURLM_CALL_MULTI_PERFORM);
            if ( $execrun != CURLM_OK ) break;
            while(true==($done = curl_multi_info_read($mh))){
                foreach($listener_list as $done_url=>$listener){
                    if($listener === $done['handle']){
                        $this->http_data[$done_url] = $this->GetData(curl_multi_getcontent($done['handle']), $done['handle']);
                        if($this->http_data[$done_url]['code'] != 200){
                            $result[$done_url] = false;
                        }else{
                            $result[$done_url] = $this->http_data[$done_url]['data']; 
                        }
                        curl_close($done['handle']);
                        curl_multi_remove_handle($mh, $done['handle']);
                        unset($listener_list[$done_url],$listener);
                        $done_num++;
                        if($multi_list){
                            $current_url = array_shift($multi_list);
                            $current = $this->_create($current_url, $timeout);
                            curl_multi_add_handle($mh, $current);
                            $listener_list[$current_url] = $current;
                            unset($current);
                            $list_num++;
                        } 
                        break;
                    }
                }
            }
            if ($done_num>=$list_num)break;
        } while (true);
        curl_multi_close($mh);
        return $result;
    }
    public function GetResultData(){
        return $this->http_data;
    } 
    protected function GetData($data,$ch){
        $header_size  = substr($data,0,5)=='HTTP/' ? curl_getinfo($ch, CURLINFO_HEADER_SIZE) : 0;
        $result['code']   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['data']   = substr($data, $header_size);
        $result['header'] = explode("\r\n", substr($data, 0, $header_size));
        $result['time']   = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        return $result;
    }
    protected function ClearSet(){
        $this->_option = array();
        $this->header = array();
        $this->ip = null;
        $this->cookies = null;
        $this->referer = null;
        $this->_post_data = array();
    }
}