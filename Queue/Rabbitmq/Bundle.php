<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/2/9
 * Time: 下午4:04
 */
namespace Quan\System\Queue\Rabbitmq;

use Phalcon\Di;
use Phalcon\DiInterface;

class Bundle implements Di\InjectionAwareInterface
{

    protected $_dependencyInjector;

    protected $_eventsManager;

    protected $_config = null;


    public static $type = [
        'direct' => AMQP_EX_TYPE_DIRECT,
        'fanout' => AMQP_EX_TYPE_FANOUT,
        'topic' => AMQP_EX_TYPE_TOPIC,
        'headers' => AMQP_EX_TYPE_HEADERS,
    ];

    public static $flags = [
        'durable' => AMQP_DURABLE,
        'exclusive' => AMQP_EXCLUSIVE,
    ];


    /**
     * Bundle constructor.
     * @param string $basename
     * @param DiInterface|null $dependencyInjector
     * @throws Exception
     */
    public function __construct($basename = '', DiInterface $dependencyInjector = null)
    {
        $this->setDI($dependencyInjector);

        $config = $this->getDI()->get('config');

        $this->_config = $config[$basename];

        if (is_null($this->_config)) {
            throw new Exception(sprintf("File %s.yaml can't be load, please check you config file or path!", $basename));
        }
    }

    public function setDI(DiInterface $dependencyInjector)
    {
        if (!is_object($dependencyInjector)) {
            $dependencyInjector = Di::getDefault();
        }

        $this->_dependencyInjector = $dependencyInjector;
    }

    public function getDI()
    {
        return $this->_dependencyInjector;
    }


    public function getConfig($item = '')
    {
        return empty($item) ? $this->_config : $this->_config[$item];
    }


    /***
     * 向叫换机发布消息
     * @param string $key
     * @param $message
     * @internal \Rabbitmq $rabbitmq
     * @return int
     */
    public function publish($key = 'thread', $message)
    {

        $config = $this->getConfig('producers')->$key;

        if (!$config) {
            throw new Exception(sprintf("no producers config on key '%s', please checke yaml config of rabbitmq", $key));
        }

        $rabbitmq = $this->getDI()->get('rabbitmq');

        $rabbitConnection = $rabbitmq->getConnection($config->connection);
        if (!$rabbitConnection->isConnected()) {
            $rabbitConnection->connect();
        }

        $exchangeName = $config->exchange['name'];


        if (!isset($rabbitmq->exchanges[$exchangeName])) {
            $channel = new \AMQPChannel($rabbitConnection);
            $exchangeType = self::getExchangeType($config->exchange['type']);
            $exchange = new \AMQPExchange($channel);
            $exchange->setType(self::getExchangeType($exchangeType));
            $exchange->setName($exchangeName);
            $exchange->declareExchange();
        } else {
            $exchange = $rabbitmq->exchanges[$exchangeName];
        }

        $routeKeys = (array)$config->route;
        $count = 0;
        foreach ($routeKeys as $routeKey) {
            $count += $exchange->publish($message, $routeKey) ? 1 : 0;
        }

        return $count;
    }

    /**
     * 获取绑定的队列
     * @param string $key
     * @return \AMQPQueue|null
     * @throws Exception
     */
    public function getQueue($key = 'thread')
    {
        $config = $this->getConfig('consumers')->get($key, null);

        if (!$config) {
            throw new Exception(sprintf('cant find key %s in your yaml config file !', $key));
        }

        $rabbitmq = $this->getDI()->get('rabbitmq');

        $rabbitConnection = $rabbitmq->getConnection($config->connection);
        if (!$rabbitConnection->isConnected()) {
            $rabbitConnection->connect();
        }

        $exchangeName = $config->exchange['name'];
        $channel = new \AMQPChannel($rabbitConnection);
        $exchangeType = self::getExchangeType($config->exchange['type']);
        $exchange = new \AMQPExchange($channel);
        $exchange->setType(self::getExchangeType($exchangeType));
        $exchange->setName($exchangeName);
        $exchange->declareExchange();

        $queueName = trim($config->queue['name']);
        $queueFlags = $config->queue['flags'];
        $queue = new \AMQPQueue($channel);
        self::setQueueFlags($queue, $queueFlags->toArray());
        if (!empty($queueName)) {
            $queue->setName($queueName);
        }
        $queue->declareQueue();


        $routeKeys = (array)$config->route;
        if ($routeKeys) {
            foreach ($routeKeys as $routeKey) {
                $queue->bind($exchange->getName(), $routeKey);
            }
            return $queue;
        } else {
            return null;
        }
    }



    public static function getExchangeType($key = 'direct')
    {
        return self::$type[$key];
    }

    public static function setQueueFlags(\AMQPQueue &$queue, array $flags = [])
    {
        foreach ($flags as $flag) {
            if (isset(self::$flags[$flag])) {
                $queue->setFlags(self::$flags[$flag]);
            }
        }
    }
}