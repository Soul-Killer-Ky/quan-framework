<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/2/4
 * Time: 下午2:47
 */
namespace Quan\System\Mvc;

use Phalcon\Di;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\DiInterface;
use Phalcon\Mvc\EntityInterface;
use Quan\System\Mvc\Struct\ManagerInterface;

abstract class Struct implements \Serializable, InjectionAwareInterface, EntityInterface, StructInterface
{
    protected $_id;

    protected $_dependencyInjector;

    protected $_structsManager;

    protected $_connection;

    protected $_skipped = false;

    const OP_NONE = 0;
    const OP_CREATE = 1;
    const OP_UDPATE = 2;
    const OP_DELETE = 3;

    public function __construct(DiInterface $dependencyInjector = null, ManagerInterface $structManager = null)
    {
        if (!is_object($dependencyInjector)) {
            $dependencyInjector = Di::getDefault();
        }

        if (!is_object($dependencyInjector)) {
            throw new \Exception('A dependency injector container is reqiured to obtian thre services related to the Struct');
        }

        $this->_dependencyInjector = $dependencyInjector;

        if (!is_object($structManager)) {
            $structManager = $dependencyInjector->getShared('structsManager');
            if (!is_object($structManager)) {
                throw new \Exception('The injected service "structsManager" is not valid');
            }
        }

        $this->_structsManager = $structManager;

        $structManager->initialize($this);

        if (method_exists($this, 'onConstruct')) {
            $this->onConstruct();
        }
    }

    public function setConnectionService($connectionService = '')
    {
        $this->_structsManager->setConnectionService($this, $connectionService);
        return $this;
    }

    public function getConnectionService()
    {
        return $this->_structsManager->getConnectionService($this);
    }

    public function getConnection()
    {
        if (!is_object($this->_connection)) {
            $this->_connection = $this->_structsManager->getConnection($this);
        }

        return $this->_connection;
    }

    public function readAttribute($attribute)
    {
        if (!isset($this->$attribute)) {
            return null;
        }
        return $this->$attribute;
    }

    public function writeAttribute($attribute, $value)
    {
        $this->$attribute = $value;
    }


    public function skipOperation($skip)
    {
        $this->_skipped = $skip;
    }

    public function serialize()
    {
        // TODO: Implement serialize() method.
    }

    public function unserialize($serialized)
    {
        // TODO: Implement unserialize() method.
    }

    public function setDI(\Phalcon\DiInterface $dependencyInjector)
    {
        // TODO: Implement setDI() method.
    }

    public function getDI()
    {
        // TODO: Implement getDI() method.
    }

    public function getId()
    {
        // TODO: Implement getId() method.
    }

    public function setId($id)
    {
        // TODO: Implement setId() method.
    }

    public function getReversedAttributes()
    {
        // TODO: Implement getReversedAttributes() method.
    }

    public function fireEvent($eventName)
    {
        // TODO: Implement fireEvent() method.
    }

    public function fireEventCancel($eventName)
    {
        // TODO: Implement fireEventCancel() method.
    }

    public function save($key, $value = null, $ttl = 0)
    {
        // TODO: Implement save() method.
    }

    public function delete($key)
    {
        // TODO: Implement delete() method.
    }

    public function flush()
    {
        // TODO: Implement flush() method.
    }


    public static function process($method, array $args = [])
    {
        /** @var Struct $struct */
        $className = get_called_class();
        $struct = new $className;
        $connection = $struct->getConnection();
        return call_user_func_array([$connection, $method], $args);
    }

}