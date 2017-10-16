<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/1/25
 * Time: 下午3:56
 */
namespace Quan\System\Cache\Backend;

use Phalcon\Cache\FrontendInterface;

/**
 * Class Redis
 * ==============
 * 1. phalcon 原来的策略是把 frontend 和 backend 用适配器的形式适配使用
 * 2. 这个Redis 分离出来的主要目的是，为了更好的实现结构化和半结构化的缓存，适配业务需求制定不同的粒度
 * 3. 参考Phalcon\Mvc\Collection 实现了 Model层
 * 4. `structsManager` 共享服务管理连接的复用 , 一个依赖注入只有一项 `structsManager`
 * ==============
 * @package Quan\System\Cache\Backend
 * @property \Redis $_redis;
 */
class Redis
{
    protected $_frontend;            # 保留，以前使用

    protected $_prefix;

    protected $_options;

    public function __construct(FrontendInterface $frontend = null, array $options = [])
    {
        $default = [
            'host'       => '127.0.0.1',
            'port'       => 6379,
            'index'      => 0,
            'persistent' => false,
            'statsKey'   => '',
        ];

        if (!is_array($options)) {
            $options = array();
        }

        $balance = count(array_filter($options, 'is_numeric', ARRAY_FILTER_USE_KEY)) == count($options);

        if (false == $balance) {
            $options = array_merge($default, $options);
            if (isset($options['prefix']) && $prefix = $options['prefix']) {
                $this->_prefix = $prefix;
            }
        } else {
            foreach ($options as &$option) {
                $option = array_merge($default, $option);
            }

            if (isset($options[0]['prefix']) && $prefix = $options[0]['prefix']) {
                $this->_prefix = $prefix;
            }
        }

        $this->_balanced = $balance;
        $this->_frontend = $frontend;
        $this->_options = $options;
    }

    public function _connect()
    {
        if ($this->_balanced === true) {
            $this->_connectMultiple();
        } else {
            $this->_connectSingal();
        }
    }

    public function _connectMultiple()
    {
        $options = $this->_options;

        $config = array_map(function ($v) { return "{$v['host']}:{$v['port']}"; }, $options);
        $timeout    = $options[0]['timeout'] ? intval($options[0]['timeout']) : 3;
        $redis = new \RedisArray($config, array('retry_timeout' => $timeout));
        $this->_redis = $redis;
    }


    public function _connectSingal()
    {
        $options = $this->_options;

        if (!extension_loaded('redis')) {
            throw new \Exception('Redis Extension doesn\'t existed');
        }

        $redis = new \Redis();

        if (!isset($options['host']) || !isset($options['port']) || !isset($options['persistent'])) {
            throw new \Exception("Unexpected inconsistency in options");
        }

        $persistent = $options['persistent'];
        $host       = $options['host'];
        $port       = $options['port'];
        $timeout    = $options['timeout'] ? intval($options['timeout']) : 3;

        if ($persistent) {
            $success = $redis->pconnect($host, $port, $timeout);
        } else {
            $success = $redis->connect($host, $port, $timeout);
        }

        if (!$success) {
            throw new \Exception("Could not connect to the Redisd server ". $host. ":". $port);
        }

        if (isset($options['auth']) && !empty($options['auth'])) {
            $success = $redis->auth($options['auth']);

            if (!$success) {
                throw new \Exception("Failed to authenticate with the Redisd server");
            }
        }

        if (isset($options['index']) && $options['index'] > 0) {
            $success = $redis->select($options['index']);

            if (!$success) {
                throw new \Exception("Redis server selected database failed");
            }
        }

        $this->_redis = $redis;
    }

    public function __call($name, $arguments)
    {
        $redis = $this->_redis;

        if (!is_object($redis)) {
            $this->_connect();
            $redis = $this->_redis;
        }
        return call_user_func_array([$redis, $name], $arguments);
    }
}