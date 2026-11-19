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
 * 请求控制流信号
 *
 * 用法：
 *   throw new Request();                          // 仅终止
 *   throw Request::stop();                        // 仅终止
 *   throw Request::abort(404,'Not Found');        // 状态码 + 文本
 *   throw Request::redirect('/login');            // 302 跳转
 *   throw Request::json(['ok'=>1]);               // JSON 响应
 *   throw Request::text('done');                  // 纯文本
 *   throw Request::html('<h1>Hi</h1>');           // HTML
 *   throw (new Request(403))->header('X-Reason','denied');
 */
class Request extends \Exception{
    /**
     * 响应状态码
     * @var int
     */
    private $status=200;
    /**
     * 响应头 名称=>值
     * @var array
     */
    private $headers=array();
    /**
     * 响应体，null表示沿用已缓冲的输出
     * @var string|null
     */
    private $body=null;
    /**
     * @param int $status
     * @param array $headers
     * @param string|null $body
     * @param string $message
     */
    public function __construct($status=200,array $headers=array(),$body=null,$message=''){
        parent::__construct($message);
        $this->status=intval($status);
        $this->headers=$headers;
        $this->body=$body;
    }
    /**
     * 响应状态码
     * @return int
     */
    public function getStatus(){
        return $this->status;
    }
    /**
     * 响应头集合
     * @return array
     */
    public function getHeaders(){
        return $this->headers;
    }
    /**
     * 响应体
     * @return string|null
     */
    public function getBody(){
        return $this->body;
    }
    /**
     * 追加/覆盖响应头
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function header($name,$value){
        $this->headers[$name]=$value;
        return $this;
    }
    /**
     * 设置响应体
     * @param string $body
     * @return $this
     */
    public function body($body){
        $this->body=(string)$body;
        return $this;
    }
    /**
     * 仅终止请求
     * @return static
     */
    public static function stop(){
        return new static();
    }
    /**
     * 以状态码终止
     * @param int $status
     * @param string $message
     * @return static
     */
    public static function abort($status=404,$message=''){
        return new static($status,array('Content-Type'=>'text/plain; charset=UTF-8'),$message);
    }
    /**
     * 跳转
     * @param string $url
     * @param int $status
     * @return static
     */
    public static function redirect($url,$status=302){
        return new static($status,array('Location'=>$url));
    }
    /**
     * JSON 响应
     * @param mixed $data
     * @param int $status
     * @return static
     */
    public static function json($data,$status=200){
        return new static(
            $status,
            array('Content-Type'=>'application/json; charset=UTF-8'),
            json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)
        );
    }
    /**
     * 纯文本响应
     * @param string $text
     * @param int $status
     * @return static
     */
    public static function text($text,$status=200){
        return new static($status,array('Content-Type'=>'text/plain; charset=UTF-8'),(string)$text);
    }
    /**
     * HTML 响应
     * @param string $html
     * @param int $status
     * @return static
     */
    public static function html($html,$status=200){
        return new static($status,array('Content-Type'=>'text/html; charset=UTF-8'),(string)$html);
    }
}