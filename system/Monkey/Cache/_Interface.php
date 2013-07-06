<?php
namespace Monkey\Cache;

/**
 * 缓存接口\Monkey\Cache\_Interface
 * @package    \Monkey\Cache
 * @author     HuangYi
 * @copyright  Copyright (c) 2011-07-01——2013-03-30
 * @license    New BSD License
 * @version    $Id: \Monkey\Cache\_Interface.php 版本号 2013-03-30 $
 */
interface _Interface
{

    /**
     * 设置缓存
     * @param string $key 要设置的缓存项目名称
     * @param mixed $value 要设置的缓存项目内容
     * @param int $time 要设置的缓存项目的过期时长，默认保存时间为 -1，永久保存为 0
     * @return bool 保存是成功为true ，失败为false
     */
    public function store($key,$value,$time=-1);
    /**
     * 读取缓存
     * @param string $key       要读取的缓存项目名称
     * @param mixed &$result    要保存的结果地址
     * @return bool             成功返回true，失败返回false
     */
    public function fetch($key, &$result);
    /**
     * 清除缓存
     * @return $this
     */
    public function clear();
    /**
     * 删除缓存单元
     * @param string $key
     * @return $this
     */
    public function delete($key);
}