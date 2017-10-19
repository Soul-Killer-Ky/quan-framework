<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/19
 * Time: 下午5:13
 */
namespace Quan\System\Mvc\View;

use Phalcon\Events\Manager as EventManager;
use Phalcon\Events\Event as PhalconEvent;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Events\Event as MainEvent;

class Event extends EventManager
{
    private static $_hookClassName = "Viewable";

    private $_class = null;
    
    private $_instance = null;


    public static function instance($moduleName = '', $appConfig = [])
    {
        try {
            $className = self::$_hookClassName;
            $namespaceRoot = $appConfig->namespace_root;
            $moduleName = ucfirst(strtolower($moduleName));
            if ($namespaceRoot && $moduleName) {
                $fullClassName = implode('\\', [$namespaceRoot, $moduleName, 'Events', $className]);
            } else {
                $fullClassName = $className;
            }

            $reflectionClass = new \ReflectionClass($fullClassName);
            return $reflectionClass->newInstance();
        } catch (\ReflectionException $e) {
            return new self();
        }
    }
}