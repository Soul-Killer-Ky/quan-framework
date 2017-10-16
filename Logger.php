<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 下午2:54
 */

namespace Quan\System;

use Phalcon\Di;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\DiInterface;
use Phalcon\Logger\Adapter\File;
use Phalcon\Logger\AdapterInterface;
use Phalcon\Logger\FormatterInterface;

/**
 * Class Logger
 * @property AdapterInterface[] $loggers
 * @package Quan\System
 */
class Logger implements InjectionAwareInterface, AdapterInterface, \ArrayAccess
{
    protected $loggers = [];

    protected $formatter = null;

    protected $level = null;

    protected $_dependencyInjector = '';

    protected $defaultFileExtension = 'log';


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

    public function setFormatter(FormatterInterface $formatter)
    {
        foreach ($this->loggers as $logger) {
            $logger->setFormatter($formatter);
        }
        $this->formatter = $formatter;
    }

    public function getFormatter()
    {
        return $this->formatter;
    }

    public function setLogLevel($level)
    {
        foreach ($this->loggers as $logger) {
            $logger->setLogLevel($level);
        }
        $this->level = $level;
    }

    public function getLogLevel()
    {
        return $this->level;
    }

    public function log($type, $message = null, array $context = null, $filename = '')
    {
        $this[$filename]->log($type, $message, $context);
    }

    public function debug($message, array $context = null, $filename = '')
    {
        $this->log(\Phalcon\Logger::DEBUG, $message, $context, $filename);
    }

    public function alert($message, array $context = null, $filename = '')
    {
        $this->log(\Phalcon\Logger::ALERT, $message, $context, $filename);
    }

    public function info($message, array $context = null, $filename = '')
    {
        $this->log(\Phalcon\Logger::INFO, $message, $context, $filename);
    }

    public function warning($message, array $context = null, $filename = '')
    {
        $this->log(\Phalcon\Logger::WARNING, $message, $context, $filename);
    }

    public function error($message, array $context = null, $filename = '')
    {
        $this->log(\Phalcon\Logger::ERROR, $message, $context, $filename);
    }

    public function notice($message, array $context = null, $filename = '')
    {
        $this->log(\Phalcon\Logger::NOTICE, $message, $context, $filename);
    }

    public function emergency($message, array $context = null, $filename = '')
    {
        $this->log(\Phalcon\Logger::EMERGENCY, $message, $context, $filename);
    }

    public function begin()
    {
        // TODO: Implement begin() method.
    }

    public function close()
    {
        foreach ($this->loggers as $logger) {
            $logger->close();
        }
    }

    public function rollback()
    {
        // TODO: Implement rollback() method.
    }


    public function commit()
    {
        // TODO: Implement commit() method.
    }


    public function offsetGet($offset)
    {
        if (!$this->loggers[$offset]) {
            $offset = basename($offset);
            $extension = pathinfo($offset, PATHINFO_EXTENSION);
            $extension = $extension ? : $this->defaultFileExtension;
            $basename = pathinfo($offset, PATHINFO_FILENAME);
            $logRealFileName = sprintf('%s_%s.%s', $basename, date('Ymd'), $extension);
            $this->loggers[$offset] = new File($this->setLogPath($logRealFileName));
        }
        return $this->loggers[$offset];
    }

    public function offsetUnset($offset)
    {
        unset($this->loggers[$offset]);
    }

    public function offsetExists($offset)
    {
        return isset($this->loggers[$offset]);
    }

    public function offsetSet($offset, $value)
    {
        $this->loggers[$offset] = $value;
    }

    /**
     * @param string $filename
     * @param DiInterface $di
     * @return string
     */
    public function setLogPath($filename = '', $di = null)
    {
        if (!$di instanceof DiInterface) {
            $di = $this->getDI();
        }

        if ($router = $di->get('router')) {
            $module = $router->getModuleName();
        } else {
            $module = 'admin';
        }

        // @todo 后期可以优化路径是否存在，如果生成了runtime，则不生成
        $dir = implode(DIRECTORY_SEPARATOR, [rtrim(RUNTIME_PATH, DIRECTORY_SEPARATOR), $module, 'logs']);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $this->_logpath = $dir;
        return $filename ? implode(DIRECTORY_SEPARATOR, [$dir, $filename]) : $dir;
    }
}