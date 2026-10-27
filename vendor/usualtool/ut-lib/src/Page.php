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
 * 实例化分页
 * 示例:$Page=new UTPage($totalpage,$page,$pagenum,$pagelink);echo $Page->ShowPager();
 * 默认支持bootstrap样式
 */
class Page{
    var $nums;
    var $current_page;
    var $sub_pages;
    var $pagenums;
    var $each_num;
    var $page_array = array();
    var $subpage_link;
    var $subpage_type;
    var $_lang = array( 
        'index_page' => '<ut data-localize="l.firstpage">首页</ut>', 
        'pre_page' => '<ut data-localize="l.previouspage">上页</ut>', 
        'next_page' => '<ut data-localize="l.nextpage">下页</ut>', 
        'last_page' => '<ut data-localize="l.lastpage">尾页</ut>', 
        'current_page' => '', 
        'total_page' => '<ut data-localize="l.totalpage">总页数</ut>:', 
        'current_show' => '<ut data-localize="l.currentpage">当前显示</ut>:', 
        'total_record' => '<ut data-localize="l.totalnum">总记录数</ut>:'
    ); 
    function __construct($total_page,$current_page,$sub_pages,$subpage_link,$subpage_type=2){ 
        $this->Pager($total_page,$current_page,$sub_pages,$subpage_link,$subpage_type); 
    }
    /**
     * 执行分页
     * @param int $total_page 总页数
     * @param int $current_page 当前页数
     * @param int $sub_pages 每页显示数
     * @param string $subpage_link 分页链接
     * @param int $subpage_type 分页类型，默认为2
     */
    function Pager($total_page,$current_page,$sub_pages=10,$subpage_link='',$subpage_type=2){ 
        if(!$current_page){ 
            $this->current_page=1; 
        }else{ 
            $this->current_page=intval($current_page); 
        } 
        $this->sub_pages=intval($sub_pages); 
        $this->pagenums=ceil($total_page); 
        $rewrite=$this->RewriteMode(); 
        if($rewrite==0 && !$this->HasModuleParam($subpage_link)){ 
            $subpage_link=$this->CurrentUrl(); 
        } 
        $this->subpage_link=$this->BuildLink($subpage_link,$rewrite); 
        $this->subpage_type = $subpage_type; 
        $this->each_num=5;
    }
    private function RewriteMode(){ 
        $rewrite=0; 
        if(defined('UTF_ROOT') && UTF_ROOT!==''){ 
            $config=Inc::GetConfig(); 
            if(isset($config['REWRITE']) && intval($config['REWRITE'])===1){ 
                $rewrite=1; 
            } 
        } 
        return $rewrite; 
    }
    private function HasModuleParam($link){ 
        if($link===null || $link===''){ 
            return false; 
        } 
        $query=''; 
        $pos=strpos($link,'?'); 
        if($pos!==false){ 
            $query=substr($link,$pos+1); 
        } 
        if($query===''){ 
            return false; 
        } 
        $params=array(); 
        parse_str($query,$params); 
        return isset($params['m']) || isset($params['p']); 
    }
    private function PathCarriesRoute($link){ 
        $path=(string)$link; 
        $pos=strpos($path,'?'); 
        if($pos!==false){ 
            $path=substr($path,0,$pos); 
        } 
        $pos=strpos($path,'#'); 
        if($pos!==false){ 
            $path=substr($path,0,$pos); 
        } 
        if(preg_match('#^[a-z][a-z0-9+.\-]*://#i',$path)){ 
            $path=(string)parse_url($path,PHP_URL_PATH); 
        } 
        $segment=array_values(array_filter(explode('/',trim($path,'/')))); 
        $config=Inc::GetConfig(); 
        $apppath=parse_url($config['APPURL'] ?? '',PHP_URL_PATH); 
        $apppath=trim((string)$apppath,'/'); 
        if($apppath!==''){ 
            $appsegment=array_values(array_filter(explode('/',$apppath))); 
            $num=count($appsegment); 
            if($num>0 && count($segment)>=$num && array_slice($segment,0,$num)===$appsegment){ 
                $segment=array_slice($segment,$num); 
            } 
        } 
        $develop=trim($config['DEVELOP'] ?? '','/'); 
        if($develop!=='' && isset($segment[0]) && $segment[0]===$develop){ 
            array_shift($segment); 
        } 
        return count($segment)>=2; 
    }
    private function CurrentUrl(){ 
        $uri=$_SERVER['REQUEST_URI'] ?? ''; 
        if($uri===''){ 
            $uri=($_SERVER['PHP_SELF'] ?? '/'); 
            $qs=$_SERVER['QUERY_STRING'] ?? ''; 
            if($qs!==''){ 
                $uri.='?'.$qs; 
            } 
        } 
        return $uri; 
    }
    private function BuildLink($link,$rewrite=0){ 
        $link=(string)$link; 
        $frag=''; 
        $pos=strpos($link,'#'); 
        if($pos!==false){ 
            $frag=substr($link,$pos); 
            $link=substr($link,0,$pos); 
        } 
        $base=$link; 
        $query=''; 
        $pos=strpos($link,'?'); 
        if($pos!==false){ 
            $base=substr($link,0,$pos); 
            $query=substr($link,$pos+1); 
        } 
        if($base===''){ 
            $current=$this->CurrentUrl(); 
            $cpos=strpos($current,'?'); 
            $cbase=($cpos===false) ? $current : substr($current,0,$cpos); 
            $cquery=($cpos===false) ? '' : substr($current,$cpos+1); 
            $base=($cbase==='') ? '/' : $cbase; 
            if($query===''){ 
                $query=$cquery; 
            } 
        } 
        $query=$this->RemoveParam($query,'page'); 
        if($rewrite==1 && $this->PathCarriesRoute($base)){ 
            $query=$this->RemoveParam($query,'m'); 
            $query=$this->RemoveParam($query,'p'); 
        } 
        $link=$base.($query!=='' ? '?'.$query : ''); 
        if(strpos($link,'?')===false){ 
            $link.='?page='; 
        }elseif(substr($link,-1)==='?' || substr($link,-1)==='&'){ 
            $link.='page='; 
        }else{ 
            $link.='&page='; 
        } 
        return $link.$frag; 
    }
    private function RemoveParam($query,$name){ 
        if($query===''){ 
            return ''; 
        } 
        $out=array(); 
        foreach(explode('&',$query) as $item){ 
            if($item===''){ 
                continue; 
            } 
            $pair=explode('=',$item); 
            if($pair[0]===$name){ 
                continue; 
            } 
            $out[]=$item; 
        } 
        return implode('&',$out); 
    }
    /**
     * 展示分页
     */
    function ShowPager(){ 
        if($this->subpage_type == 1){ 
            return $this->PageListOne(); 
        }elseif ($this->subpage_type == 2){ 
            return $this->PageListTwo(); 
        } 
    }
    function InitArray(){ 
        for($i=0;$i<$this->each_num;$i++){ 
            $this->page_array[$i]=$i; 
        } 
        return $this->page_array; 
    }
    function ConstructNumPage(){ 
        if($this->pagenums < $this->each_num){
            $current_array=array();
            for($i=0;$i<$this->pagenums;$i++){
                $current_array[$i]=$i+1;
            }
        }else{
            $current_array=$this->InitArray();
            if($this->current_page <=3 && $this->current_page>0){
                for($i=0;$i<count($current_array);$i++){
                $current_array[$i]=$i+1;
                }
            }elseif($this->current_page <= $this->pagenums && $this->current_page > $this->pagenums - $this->each_num + 1 ){
                for($i=0;$i<count($current_array);$i++){
                $current_array[$i]=($this->pagenums)-($this->each_num)+1+$i;
                }
            }else{
                for($i=0;$i<count($current_array);$i++){
                $current_array[$i]=$this->current_page-2+$i;
                }
            }
        }
        return $current_array; 
    }
    /**
     * 分页类型1
     */
    function PageListOne(){ 
        $subPageCss1Str="<div class='dataTables_paginate paging_simple_numbers'><ul class='pagination'>"; 
        $subPageCss1Str."<li class='paginate_button page-item disabled'><a class='page-link ut-disabled'>". $this->_lang['current_page'] . $this->current_page." / " .$this->pagenums."</a></li>"; 
        if($this->current_page > 1){ 
            $firstPageUrl=$this->subpage_link."1"; 
            $prewPageUrl=$this->subpage_link.($this->current_page-1); 
            $subPageCss1Str.="<li class='paginate_button page-item'><a href='$firstPageUrl' class='page-link'>{$this->_lang['index_page']}</a></li>"; 
            $subPageCss1Str.="<li class='paginate_button page-item'><a href='$prewPageUrl' class='page-link'>{$this->_lang['pre_page']}</a></li>"; 
        }else { 
            $subPageCss1Str.="<li class='paginate_button page-item disabled'><a class='page-link ut-disabled'>{$this->_lang['index_page']}</a></li>"; 
            $subPageCss1Str.="<li class='paginate_button page-item disabled'><a class='page-link ut-disabled'>{$this->_lang['pre_page']}</a></li>"; 
        }   
        if($this->current_page < $this->pagenums){ 
            $lastPageUrl=$this->subpage_link.$this->pagenums; 
            $nextPageUrl=$this->subpage_link.($this->current_page+1); 
            $subPageCss1Str.="<li class='paginate_button page-item'><a href='$nextPageUrl' class='page-link'>{$this->_lang['next_page']}</a></li>"; 
            $subPageCss1Str.="<li class='paginate_button page-item'><a href='$lastPageUrl' class='page-link'>{$this->_lang['last_page']}</a></li>"; 
        }else{ 
            $subPageCss1Str.="<li class='paginate_button page-item disabled'><a class='page-link ut-disabled'>{$this->_lang['next_page']}</a></li>"; 
            $subPageCss1Str.="<li class='paginate_button page-item disabled'><a class='page-link ut-disabled'>{$this->_lang['last_page']}</a></li>"; 
        } 
        $subPageCss1Str.="</ul></div>";
        return $subPageCss1Str; 
    }
    private function CompactPages($size=3){ 
        $nums=array(); 
        $total=intval($this->pagenums); 
        $current=intval($this->current_page); 
        if($total<=0){ 
            return $nums; 
        } 
        if($total<=$size){ 
            for($i=1;$i<=$total;$i++){ 
                $nums[]=$i; 
            } 
            return $nums; 
        } 
        $start=$current; 
        if($start<1){ 
            $start=1; 
        } 
        if($start+$size-1>$total){ 
            $start=$total-$size+1; 
        } 
        for($i=0;$i<$size;$i++){ 
            $nums[]=$start+$i; 
        } 
        return $nums; 
    }
    /**
     * 分页类型2
     */
    function PageListTwo(){ 
	    $subPageCss2Str="<div class='dataTables_paginate paging_simple_numbers'><ul class='pagination ut-pager'>"; 
	    $subPageCss2Str.="<li class='paginate_button page-item disabled'><a class='page-link ut-disabled'>".$this->_lang['current_page'] . $this->current_page."/" . $this->pagenums."</a></li>"; 
	    if($this->current_page > 1){ 
		    $firstPageUrl=$this->subpage_link."1"; 
		    $prewPageUrl=$this->subpage_link.($this->current_page-1); 
		    $subPageCss2Str.="<li class='paginate_button page-item'><a href='$firstPageUrl' class='page-link ut-pager-link' title='".strip_tags($this->_lang['index_page'])."'>&laquo;</a></li>"; 
		    $subPageCss2Str.="<li class='paginate_button page-item'><a href='$prewPageUrl' class='page-link ut-pager-link' title='".strip_tags($this->_lang['pre_page'])."'>&lsaquo;</a></li>"; 
	    }else { 
		    $subPageCss2Str.="<li class='paginate_button page-item disabled'><a class='page-link ut-disabled' title='".strip_tags($this->_lang['index_page'])."'>&laquo;</a></li>"; 
		    $subPageCss2Str.="<li class='paginate_button page-item disabled'><a class='page-link ut-disabled' title='".strip_tags($this->_lang['pre_page'])."'>&lsaquo;</a></li>"; 
	    } 
	    $a=$this->CompactPages(3); 
	    for($i=0;$i<count($a);$i++){ 
		    $s=$a[$i]; 
		    if($s == $this->current_page){ 
		        $subPageCss2Str.="<li class='paginate_button page-item active'><a class='page-link ut-pager-link ut-pager-active'>{$s}</a></li>"; 
		    }else{ 
		        $url=$this->subpage_link.$s; 
		        $subPageCss2Str.="<li class='paginate_button page-item'><a class='page-link ut-pager-link' href='{$url}'>{$s}</a></li>"; 
		    } 
	    } 
	    if($this->current_page < $this->pagenums){ 
		    $lastPageUrl=$this->subpage_link.$this->pagenums; 
		    $nextPageUrl=$this->subpage_link.($this->current_page+1); 
		    $subPageCss2Str.="<li class='paginate_button page-item'><a class='page-link ut-pager-link' href='$nextPageUrl' title='".strip_tags($this->_lang['next_page'])."'>&rsaquo;</a></li>"; 
		    $subPageCss2Str.="<li class='paginate_button page-item'><a class='page-link ut-pager-link' href='$lastPageUrl' title='".strip_tags($this->_lang['last_page'])."'>&raquo;</a></li>"; 
	    }else{ 
		    $subPageCss2Str.="<li class='paginate_button page-item disabled'><a class='page-link ut-disabled' title='".strip_tags($this->_lang['next_page'])."'>&rsaquo;</a></li>"; 
	        $subPageCss2Str.="<li class='paginate_button page-item disabled'><a class='page-link ut-disabled' title='".strip_tags($this->_lang['last_page'])."'>&raquo;</a></li>"; 
	    } 
        $subPageCss2Str.="</ul></div>";
	    return $subPageCss2Str; 
    }
    /**
     * 析构函数
     */
    function __destruct(){
        unset($nums); 
        unset($current_page); 
        unset($sub_pages); 
        unset($pagenums); 
        unset($each_num);
        unset($page_array); 
        unset($subpage_link); 
        unset($subpage_type); 
    }
}