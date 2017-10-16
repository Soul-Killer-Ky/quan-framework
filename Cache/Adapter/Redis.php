<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/2/5
 * Time: 下午6:46
 */
namespace Quan\System\Cache\Adapter;

use Quan\System\Config;
use Quan\System\Cache\Backend\Redis as RedisConnection;

class Redis
{
    private $_connections = array();

    public function set($key = 'default')
    {
        $config = Config::items('redis.'. $key);
        $this->_connections[$key] = new RedisConnection(null, $config);
    }

    public function get($key = 'default')
    {
        if (!isset($this->_connections[$key])) {
            $this->set($key);
        }
        return $this->_connections[$key];
    }
}