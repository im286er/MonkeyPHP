<?php
namespace Monkey\Request;

use Monkey\_Interface\Component;

/**
 * 请求类\Monkey\Request\Request
 * @package    \Monkey\Request
 * @author     HuangYi
 * @copyright  Copyright (c) 2011-07-01——2013-03-30
 * @license    New BSD License
 * @version    $Id: \Monkey\Request\Request.php 版本号 2013-03-30 $
 */
class Request implements Component
{
    /**
     * @var \Monkey\Monkey
     */
    private $oMonkey=null;
    /**
     * @var \Monkey\Request\HttpHeader
     */
    private $oHttpHeader=null;
    private
        $parameters,
        $spider= null,
        $uriPrefix= null
    ;
    private function __construct(){
    }

    /**
     * 获取请求实例
     * @return \Monkey\Request\Request
     */
    public static function _instance(){
        static $self;
        !$self and $self=new self();
        return $self;
    }

    /**
     * 组件初始化注入
     * @param \Monkey\Monkey|null $monkey 依赖类
     * @param mixed|null $config 配置
     * @return mixed
     */
    public function _initialize($monkey = null, $config = null)
    {
        $this->oMonkey=$monkey;
        $this->parameters=array_merge($this->parameters, $_GET, $_POST);
    }

    /**
     * 设置请求参数
     * @param string $name 参数名
     * @param $value 参数值
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name]=$value;
    }

    /**
     * 获取$_GET、$_POST信息
     * @param string $name 参数名称
     * @param string $defaultValue 当获取变量失败的时候返回该值,默认该值为null
     * @return string
     */
    public function getParameter($name, $defaultValue = null)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : $defaultValue;
    }

    /**
     * 批量获取$_GET、$_POST参数值
     * @example $request->getParameters();//获取整个Request参数集
     * @example $request->getParameters(array($name1,$name2=>$default2,$name3));//获取三个Request参数，其中第二个参数有默认值。
     *
     * @param string $names 参数名称：为空则返回整个Request；允许在数组中使用默认值，方法见例二。
     * @return array
     */
    public function &getParameters($names=null)
    {
        return $this->_getByNames($names,$this->parameters);
    }

    /**
     * 获取HTTP请求头对象
     * \Monkey\Request\HttpHeader
     * @return HttpHeader
     */
    public function HttpHeader()
    {
        if($this->oHttpHeader===null)
        {
            $this->oHttpHeader= new HttpHeader();
        }
        return $this->oHttpHeader;
    }

    /**
     * 获取请求方法
     * 'GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'NONE', 'OPTIONS', 'TRACE', 'CONNECT'
     * @return string 一般为'GET'或'POST'
     */
    public function getMethod()
    {
        return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
    }

    /**
     * 获取请求的相对网址
     * 由于结果不是相对于前端控制器的，所以不常用
     * @return string
     */
    public function getUrl()
    {
        if(!isset($_SERVER['REQUEST_URI']) && isset($_SERVER['HTTP_X_REWRITE_URL']) )
        {
            $_SERVER['REQUEST_URI']=$_SERVER['HTTP_X_REWRITE_URL'];
        }
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * 获取请求的绝对网址
     * 通常是浏览器地址栏的全部信息
     * @return string
     */
    public function getUri()
    {
        if($this->isAbsUri())
            return $this->getUrl();
        else
            return $this->getUriPrefix().$this->getUrl();
    }

    /**
     * 判断是否是HTTPS安全协议
     * @return bool
     */
    public function isHttps()
    {
        if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']))
        {
            strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https'
                and
                $_SERVER['HTTPS']=1;
        }
        $https=strtolower($_SERVER['HTTPS']);
        return ($https=='on' || $https==1);
    }

    /**
     * 获取请求网址协议+域名+端口号部分
     * @return string
     */
    public function getUriPrefix()
    {
        if($this->uriPrefix!==null)
        {
            return $this->uriPrefix;
        }
        $isHttps=$this->isHttps();
        $standardPort= $isHttps?'443':'80';
        $protocol= $isHttps?'https':'http';
        $host = explode(":", $this->getHost());
        if (count($host) == 1)
        {
            $host[] = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : '';
        }
        if ($host[1] == $standardPort || empty($host[1]))
        {
            unset($host[1]);
        }
        return $this->uriPrefix = $protocol.'://'.implode(':', $host);
    }

    /**
     * 判断请求网址是否是绝对地址
     * @return bool
     */
    public function isAbsUri()
    {
        return preg_match('/^http/', $_SERVER['REQUEST_URI']);
    }

    /**
     * 获取当前uri路径的相对目录
     * @return string
     */
    public function getRelativeUrlRoot()
    {
        return FRONT_URL;
    }

    /**
     * 是否是AJAX请求
     * 支持Prototype, Mootools, jQuery等的AJAX
     * 能识别跨域ajax，跨域ajax返回true
     * @return Boolean
     */
    public function isAjax()
    {
        if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']))
            return $_SERVER['HTTP_ACCEPT']=='text/javascript, application/javascript, */*';
        else
            return strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * 是否是flash请求
     * @return bool
     */
    public function isFlash()
    {
        return isset($_SERVER['HTTP_USER_AGENT'])
            &&
            (
                stripos($_SERVER['HTTP_USER_AGENT'],'Shockwave')!==false
                    ||
                stripos($_SERVER['HTTP_USER_AGENT'],'Flash')!==false
            ) ;
    }

    /**
     * 获取$_ENV信息（environment）
     * @param string $name $_ENV的键值名称
     * @param string $defaultValue 当获取变量失败的时候返回该值,默认该值为null
     * @return string
     */
    public function getEnvironment($name, $defaultValue = null) {
        return isset($_ENV[$name])? $_ENV[$name] : $defaultValue;
    }

    /**
     * 批量获取$_ENV信息（environment）
     * @param array|null $name $_ENV的键值名称，为空时返回整个环境变量
     * @return array
     */
    public function getEnvironments($name = null) {
        return $this->_getByNames($name, $_ENV);
    }

    /**
     * 判断常用的爬虫函数
     * @author Mer
     * @return boolean
     */
    public function isSpider() {
        if($this->spider===null)
        {
            $this->spider=false;
            $agent   = strtolower($_SERVER['HTTP_USER_AGENT']);
            $spiders = include(__DIR__.'/data/spider.php');
            foreach($spiders as $c) {
                if(strpos($agent, $c)!==false)
                {
                    $this->spider=true;
                    break;
                }
            }
        }
        return $this->spider;
    }

    /**
     * 获取cookie变量
     * @param string $name 变量名
     * @param null $defaultValue 默认的变量值
     * @return mixed|null
     */
    public function getCookie($name,$defaultValue=null)
    {
        static $oCookie=null;
        $oCookie===null and $oCookie= $this->oMonkey->getCookie();
        return $oCookie->get($name,$defaultValue);
    }

    /**
     * 获取客户端IP
     * @return string
     */
    public function getIP(){
        static $ip=null;
        if($ip)return $ip;
        if(getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
        {
            $ip = getenv("HTTP_CLIENT_IP");
        }
        elseif(getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
        {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        }
        elseif(getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
        {
            $ip = getenv("REMOTE_ADDR");
        }
        elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        else
        {
            $ip = "unknown";
        }
        $ip==='::1' and $ip='127.0.0.1';
        return $ip;
    }

    private function _getByNames($names, &$data){
        if(!$names)
        {
            return $data;
        }
        $result = array();
        foreach ($names as $key => $value)
        {
            if(is_int($key))
                $result[$value]= isset($data[$value]) ? $data[$value] : null;
            else
                $result[$key]= array_key_exists($key, $data) ? $data[$key] : $value;
        }
        return $result;
    }

//end class
}