<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/2/4
 * Time: 下午2:50
 */
namespace Quan\System\Mvc\Struct;

use Phalcon\Di\InjectionAwareInterface;
use Phalcon\DiInterface;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\ManagerInterface as EventsManagerInterface;
use Quan\System\Mvc\StructInterface;

class Manager implements EventsAwareInterface, InjectionAwareInterface, ManagerInterface
{
    protected $_eventsManager;

    protected $_dependencyInjector;

    protected $_initialized;

    protected $_lastInitialized;

    protected $_connectionServices;

    protected $_connections;

    protected $_serviceName = 'default';

    public function setEventsManager(EventsManagerInterface $eventsManager)
    {
        $this->_eventsManager = $eventsManager;
    }

    public function getEventsManager()
    {
        return $this->_eventsManager;
    }

    public function setDI(DiInterface $dependencyInjector)
    {
        $this->_dependencyInjector = $dependencyInjector;
    }

    public function getDI()
    {
        return $this->_dependencyInjector;
    }

    public function initialize(StructInterface $struct)
    {
        $classname = get_class($struct);

        if (!isset($this->_initialized[$classname])) {

            if (method_exists($struct, 'initialize')) {
                $struct->{'initialize'}();
            }
        }

        if ($this->_eventsManager instanceof \Phalcon\Events\Manager) {
            $this->_eventsManager->fire('structManager:afterInitialize', $struct);
        }

        $this->_initialized[$classname] = $struct;
        $this->_lastInitialized = $struct;
    }

    public function isInitialized($modelName = '')
    {
        return isset($this->_initialized[strtolower($modelName)]);
    }

    public function getLastInitialized()
    {
        return $this->_lastInitialized;
    }

    public function getConnectionService(StructInterface $struct)
    {
        $service = $this->_serviceName;
        $entityName = get_class($struct);
        if (isset($this->_connectionServices[$entityName])) {
            $service = $this->_connectionServices[$entityName];
        }
        return $service;
    }

    public function setConnectionService(StructInterface $struct, $connectionService = '')
    {
        $this->_connectionServices[get_class($struct)] = $connectionService;
    }

    public function getConnection(StructInterface $struct)
    {
        $service = $this->_serviceName;
        $connectionService = $this->_connectionServices;

        if (is_array($connectionService)) {
            $entityName = get_class($struct);

            if (isset($connectionService[$entityName])) {
                $service = $connectionService[$entityName];
            }
        }

        $dependencyInjector = $this->_dependencyInjector;

        if (!$dependencyInjector instanceof DiInterface) {
            throw new \Exception('A dependency injector container is required to obtain the services related to the Struct');
        }

        // @todo 这里的连接可不不用DI
        $connection = $dependencyInjector->getShared('struct')->get($service);
        if (!is_object($connection)) {
            throw new \Exception('Invalid injected connection service');
        }

        return $connection;
    }

    /**
     * @param string $eventName
     * @param StructInterface $struct
     */
    public function notifyEvent($eventName, StructInterface $struct)
    {}

    public function setCustomEventsManager(StructInterface $struct, EventsManagerInterface $eventManager)
    {}

    public function getCustomEventsManager(StructInterface $struct)
    {}
}