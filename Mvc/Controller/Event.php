<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2016/11/23
 * Time: 下午6:41
 * 处理事件  boot、beforeStartModule、afterStartModule、beforeHandleRequest、afterHandleRequest
 * 事件管理，
 */

namespace Quan\System\Mvc\Controller;

use Phalcon\Http\Request;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Events\Event as MainEvent;
use Phalcon\Dispatcher;
use Quan\System\Response;

class Event extends Plugin
{
    private static $_hookClassName = "Hookable";

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

    public function afterExecuteRoute(MainEvent $e, Dispatcher $dispatcher)
    {
        if ($dispatcher->getDI()->has('view')) {
            $view = $dispatcher->getDI()->get('view');
            $response = $dispatcher->getDI()->get('response');
            if (is_null($view) || $view instanceof QuanStdClass) {
                $dispatcher->setReturnedValue($response);
            }

            if ($response instanceof Response && $response->getWillReturnJson()) {
                $dispatcher->setReturnedValue($response);
            }
        }
    }

    /***
     * @param MainEvent $e
     * @param Dispatcher $dispatcher
     * @param \Exception|\Throwable $exception
     */
    public function beforeException(MainEvent $e, Dispatcher $dispatcher, $exception)
    {
        /** @var Logger $logger */
        /** @var Request $request */
        $request = $this->dispatcher->getDI()->get('request');
        $logger = $dispatcher->getDI()->get('log');
        $filename = 'error.log';
        $logger->error('', null, $filename);
        $logger->error(
            "{method}: {url}",
            ['method' => $request->getMethod(), 'url' => $request->getURI()],
            $filename);
        $logger->error(
            "{class}: {message}",
            ['class' => get_class($exception), 'message' => $exception->getMessage()],
            $filename);
        $logger->error(
            "{file}({line})",
            ['file' => $exception->getFile(), 'line' => $exception->getLine()],
            $filename
        );
        $logger->error($exception->getTraceAsString(), null, $filename);
    }

}