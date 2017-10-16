<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/3/10
 * Time: 上午11:52
 */
namespace Quan\System\Cache\Backend;

use Phalcon\Cache\FrontendInterface;

/**
 * Class Libmemcached
 * @package Quan\System\Cache\Backend
 * @property FrontendInterface $_frontend
 */
class Libmemcached extends \Phalcon\Cache\Backend\Libmemcached
{
    /**
     * @var \Memcached;
     */
    protected $_memcache;

    public function getServerList()
    {
        if (!is_object($this->_memcache)) {
            $this->_connect();
        }
        return $this->_memcache->getServerList();
    }


    public function saveMulti($items, $lifetime = 0)
    {
        $frontend = $this->_frontend;

        if ($lifetime === null) {
            $tmp = $this->_lastLifetime;

            if (!$tmp) {
                $tt1 = $frontend->getLifetime();
            } else {
                $tt1 = $tmp;
			}
        } else {
            $tt1 = $lifetime;
        }

        foreach ($items as $key => $content) {
            $data[$this->_prefix. $key] = !is_numeric($content) ? $frontend->beforeStore($content) : $content;
        }

        $success = $this->_memcache->setMulti($data);
        return $success;
    }

    public function getMulti(array $keys, $lifetime = 0)
    {
        $frontend = $this->_frontend;
        $keyMap = array();

        $memcache = $this->_memcache;
        if (!is_object($memcache)) {
            $this->_connect();
        }

        foreach ($keys as $key) {
            $keyMap[$this->_prefix. $key] = $key;
        }

        $cacheContent = $this->_memcache->getMulti($keys);

        foreach ($cacheContent as &$cache) {
            $cache = $this->_frontend->afterRetrieve($cache);
        }

        return $cacheContent ? : array();
    }
}