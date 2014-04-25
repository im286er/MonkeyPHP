<?php
namespace Monkey\View;

/**
 * 分页栏生成器，支持ajax（可选） page
 */
class Page {

    private
        /**
         * @var \Monkey\App\App
         */
        $app,
        $tagNumber='{number}',
        $tagText='{text}',
        $config,
        $style,
        $_link,
        $link           ='_link',
        $linkAjax       ='_link_ajax',
        $spanCurrent    ='_span_current',
        $spanTotal      ='_span_total',
        $inputJump      ='_input_jump',
        $first          ='_text_first',//首页
        $pre            ='_text_pre',//上一页
        $next           ='_text_next',//下一页
        $last           ='_text_last',//尾页
        $loading        ='_loading',

        $currentPage,
        $barLimit,
        $totalPage,
        $isAjax=false//是否支持AJAX分页模式
    ;
    /**
     * @param \Monkey\App\App $app
     * @param array $config
     */
    public function __construct($app,$config)
    {
        $this->app=$app;
        $this->config=$config;
        $this->style=$config['page_style_name'];
    }

    /**
    * 设置AJAX模式
    * @param bool $isAjax 默认ajax触发的动作。
    * @return $this
    */
    public function setAjax($isAjax=false){
        $this->isAjax=(bool)$isAjax;
        return $this;
    }

    /**
     * 设置分页栏样式
     * @param string | null $style
     * @return $this
     */
    public function setStyle($style=null)
    {
        $this->style= $style ? $style : $this->config['page_style_name'];
        return $this;
    }

    /**
     * 通过列表条目来获取分页栏
     * @param int $currentPage  当前页码
     * @param int $totalItem  条目总数
     * @param int $listItem  每页列表中的条目容量
     * @param int $barLimit  分页栏上显示的页码数量
     * @return string
     */
    public function getByList($currentPage,$totalItem,$listItem,$barLimit=10)
    {
        $totalPage=ceil($totalItem/$listItem);
        return $this->getByPage($currentPage,$totalPage,$barLimit);
    }

    /**
     * 通过总页数来获取分页栏
     * @param int $currentPage  当前页码
     * @param int $totalPage  总页数
     * @param int $barLimit  分页栏上显示的页码数量
     * @return string
     */
    public function getByPage($currentPage,$totalPage,$barLimit=10)
    {
        if($currentPage>$totalPage or $currentPage<1 or $barLimit<1)return'';
        $this->currentPage=$currentPage;
        $this->barLimit=$barLimit;
        $this->totalPage=$totalPage;
        $this->_link=$this->config[$this->style.($this->isAjax?$this->linkAjax:$this->link)];
        $result='';
        $loading=$this->config[$this->style.$this->loading];
        foreach($loading as $method){
            $method='get_'.$method;
            if(method_exists($this,$method))
                $result.=$this->$method();
        }
        return $result;
    }

    private function get_first()
    {
        if($this->currentPage==1)return'';
        $s=str_replace($this->tagNumber,'1',$this->_link);
        return str_replace($this->tagText,$this->config[$this->style.$this->first],$s);
    }

    private function get_pre()
    {
        if($this->currentPage<2)return'';
        $s=str_replace($this->tagNumber,$this->currentPage-1,$this->_link);
        return str_replace($this->tagText,$this->config[$this->style.$this->pre],$s);
    }

    private function get_current()
    {
        return str_replace($this->tagNumber,$this->currentPage,$this->config[$this->style.$this->spanCurrent]);
    }

    private function get_next()
    {
        if($this->currentPage>=$this->totalPage)return'';
        $s=str_replace($this->tagNumber,$this->currentPage+1,$this->_link);
        return str_replace($this->tagText,$this->config[$this->style.$this->next],$s);
    }

    private function get_last()
    {
        if($this->currentPage==$this->totalPage)return'';
        $s=str_replace($this->tagNumber,$this->totalPage,$this->_link);
        return str_replace($this->tagText,$this->config[$this->style.$this->last],$s);
    }

    private function get_total()
    {
        return str_replace($this->tagNumber,$this->totalPage,$this->config[$this->style.$this->spanTotal]);
    }

    private function get_jump()
    {
        return $this->config[$this->style.$this->inputJump];
    }

    private function get_list()
    {
        $half=ceil($this->barLimit/2);
        $end= ($this->currentPage+$half>$this->totalPage ? $this->totalPage : $this->currentPage+$half);
        $begin= $end-$this->barLimit;
        if($begin<1){
            $end= $this->barLimit>$this->totalPage ?  $this->totalPage : $this->barLimit;
            $begin=1;
        }
        $end++;  $result='';
        for($i=$begin;$i<$end;$i++){
            if($i==$this->currentPage)
                $result.=str_replace($this->tagNumber,$this->currentPage,$this->config[$this->style.$this->spanCurrent]);
            else
                $result.=str_replace(array($this->tagNumber,$this->tagText),$i,$this->_link);
        }
        return $result;
    }
}