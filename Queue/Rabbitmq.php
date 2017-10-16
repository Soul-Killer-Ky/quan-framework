<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/2/6
 * Time: 上午11:56
 */
namespace Quan\System\Queue;

use \AMQPChannel, \AMQPExchange, \AMQPQueue, \AMQPConnection;
use Phalcon\Di;
use Phalcon\DiInterface;
use Quan\System\Config;
use Quan\System\Queue\Rabbitmq\Bundle;
use Quan\System\Queue\Rabbitmq\Exception as RabbitException;

/**
 * Class Rabbitmq
 * @package Quan\System\Queue
 * @property AMQPChannel $channel
 * @property AMQPConnection[] $_connections
 * @property Bundle[] $_bundles;
 * @property AMQPExchange[] $exchanges;
 */
class Rabbitmq implements Di\InjectionAwareInterface
{
    protected $_dependencyInjector;

    protected $_connections;

    protected $_bundles;

    public $exchanges = [];

    /**
     * 构造方法
     * Rabbitmq constructor.
     * @param string $item
     * @param DiInterface $dependencyInjector
     */
    public function __construct($item = 'default', DiInterface $dependencyInjector = null)
    {
        $this->setConnection($item);
    }

    public function setDI(DiInterface $dependencyInjector)
    {
        $this->_dependencyInjector = $dependencyInjector;

        if (!is_object($dependencyInjector)) {
            $this->_dependencyInjector = Di::getDefault();
        }
    }

    public function getDI()
    {
        return $this->_dependencyInjector;
    }


    /**
     * 获取某个链接实例
     * @param string $item
     * @return AMQPConnection
     */
    public function getConnection($item = 'default')
    {
        if (!isset($this->_connections[$item])) {
            $this->setConnection($item);
        }

        return $this->_connections[$item];
    }

    /**
     * 新键链接实例
     * @param string $item
     * @throws RabbitException
     */
    protected function setConnection($item = 'default')
    {
        $item = trim($item);
        $config = Config::items('rabbitmq.'. $item);

        if (!$config) {
            throw new RabbitException(sprintf('can not load config with item:[%s]', $item));
        }

        $defaultConfig = [
            'host' => '127.0.0.1',
            'port' => rand(5672, 5674),
            'vhost' => '/',
            'login' => 'guest',
            'password' => 'guest',
            // 'read_timeout'  => 3,
            'write_timeout' => 3,
            'connect_timeout' => 3,
        ];

        if (array_key_exists('slave', $config) && count($config['slave']) > 0) {
            $mainConfig = $config['slave'][array_rand($config['slave'])];
        } elseif (array_key_exists('master', $config)) {
            $mainConfig = $config['master'];
        } else {
            $mainConfig = $config;
        }

        $config = array_merge($defaultConfig, $mainConfig);
        $this->_connections[$item] = $connection = new AMQPConnection($config);
    }

    /**
     * 释构方法
     */
    public function __destruct()
    {
        foreach ($this->_connections as $connection) {
            $connection->disconnect();
        }
    }

    /**
     * connect the server
     * @param string $item
     * @return bool
     */
    public function connect($item = 'default')
    {
        $success = $this->getConnection($item)->connect();
        return $success;
    }

    /**
     * 生产者
     * @param string $item
     * @param string $key
     * @param string $message
     * @return int
     * @throws RabbitException
     */
    public function product($item = 'queue', $key = 'thread', $message = '')
    {
        if (!isset($this->_bundles[$item])) {
            $this->_bundles[$item] = new Bundle('rabbitmq_'. $item, $this->getDI());
        }

        /** @var Bundle $bundle */
        $bundle = $this->_bundles[$item];

        if (!$bundle) {
            throw new RabbitException('rabbitmq bundle is null for producers');
        }

        return $bundle->publish($key, $message);
    }

    /**
     * @param string $item
     * @param string $key
     * @return AMQPQueue|null
     * @throws RabbitException
     */
    public function getQueue($item = 'queue', $key = 'thread')
    {
        if (!isset($this->_bundles[$item])) {
            $this->_bundles[$item] = new Bundle('rabbitmq_'. $item, $this->getDI());
        }

        /** @var Bundle $bundle */
        $bundle = $this->_bundles[$item];

        if (!$bundle) {
            throw new RabbitException('rabbitmq bundle is null for producers');
        }

        return $bundle->getQueue($key);
    }
}