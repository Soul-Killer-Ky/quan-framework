<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2016/11/23
 * Time: 下午6:41
 * 处理事件  boot、beforeStartModule、afterStartModule、beforeHandleRequest、afterHandleRequest
 * 事件管理，
 */

namespace Quan\System;

use Phalcon\Http\Request;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Events\Event;
use Phalcon\Dispatcher;
use Phalcon\Logger\Adapter as LogAdapter;

class EventPlugin extends Plugin
{
    private $_hookClassName = "Hookable";

    public function beforeExecuteRoute(Event $e = null, Dispatcher $dispatcher = null)
    {
        $this->hook($e, $dispatcher);
    }

    public function beforeNotFoundAction(Event $e, Dispatcher $dispatcher)
    {
        $this->hook($e, $dispatcher);
    }

    public function afterDispatch(Event $e, Dispatcher $dispatcher)
    {
        $this->hook($e, $dispatcher);
    }

    public function afterExecuteRoute(Event $e, Dispatcher $dispatcher)
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

        $this->hook($e, $dispatcher);
    }

    /***
     * @param Event $e
     * @param Dispatcher $dispatcher
     * @param \Exception|\Throwable $exception
     */
    public function beforeException(Event $e, Dispatcher $dispatcher, $exception)
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
        $this->hook($e, $dispatcher, $exception);
    }

    /***
     * @param Event $event
     * @param Dispatcher $dispatcher
     * @param \Exception|\Throwable $exception
     * @return bool|mixed
     */
    public function hook(Event $event, Dispatcher $dispatcher, $exception = null)
    {
        try {
            $config = $dispatcher->getDI()->getConfig()['setting']->get('application', null);
            $namespaceRoot = $config ? $config->get('namespace_root', '') : null;
            $moduleName = ucfirst($dispatcher->getModuleName());
            $moduleName = $moduleName ?: $dispatcher->getTaskName();
            $className = ucfirst($this->_hookClassName);
            if ($namespaceRoot && $moduleName) {
                $fullClassName = implode('\\', [$namespaceRoot, $moduleName, 'Events', $className]);
            } else {
                $fullClassName = $className;
            }

            $reflectionClass = new \ReflectionClass($fullClassName);

            if ($reflectionClass) {
                $reflectionObject = $reflectionClass->newInstance();
                if ($reflectionClass->hasMethod($event->getType())) {
                    $reflectionMethod = new \ReflectionMethod($reflectionObject, $event->getType());
                    return $reflectionMethod->invoke($reflectionObject, $event, $dispatcher, $exception);
                }
            }
            return true;
        } catch (\ReflectionException $e) {
            return true;
        }
    }
}