<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2016/11/22
 * Time: 下午6:33
 */

namespace Quan\System\Init\Cli;
use Phalcon\Loader;
use Phalcon\DiInterface;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Cli\Dispatcher as CliDispatcher;
use Phalcon\Events\Manager as EventsManage;
use Quan\System\EventPlugin;
use Quan\System\Response;

if (!class_exists('Quan\System\Init\Cli\Module')) {

    class Module implements ModuleDefinitionInterface
    {
        /**
         * 注册自定义加载器
         */
        public function registerAutoloaders(DiInterface $di = null)
        {}


        /**
         * 注册自定义服务
         */
        public function registerServices(DiInterface $di)
        {
            $settings = $di->get('config')['setting']->application;

            $module = $di->get('router')->getModuleName();

            $loader = new Loader();

            if ($di) {
                $settings = $di->get('config')['setting']->application;
                $namespaces = [];

                foreach (['controllers', 'models', 'libraries', 'logics', 'events'] as $component) {
                    $namespaces[implode('\\', [ucfirst($settings->namespace_root), ucfirst($module), ucfirst($component)])] =
                        sprintf(APP_PATH. '/applications/%s/%s/', $module, $component);
                }

                foreach (['controllers', 'models', 'libraries', 'logics'] as $component) {
                    $namespaces[implode('\\', [ucfirst($settings->namespace_root), ucfirst('common'), ucfirst($component)])] =
                        sprintf(COMMON_PATH. '/%s/', $component);
                }



                if ($namespaces) {
                    $loader->registerNamespaces($namespaces);
                }
            }

            $loader->registerDirs([
                SYSTEM_PATH,
                COMMON_PATH. '/utils/'
            ]);

            $loader->register();

            // Registering a dispatcher
            $di->set(
                "dispatcher",
                function () use ($settings, $module) {

                    $dispatcher = new CliDispatcher();
                    $namespace= implode('\\', [ucfirst($settings->namespace_root), ucfirst($module), 'Controllers']);
                    $dispatcher->setActionSuffix('');
                    $dispatcher->setDefaultNamespace($namespace. '\\');
                    $dispatcher->setTaskSuffix('Controller');
                    $dispatcher->setDefaultTask('index');
                    $dispatcher->setModuleName($module);
                    $eventsMangager = new EventsManage();
                    $eventsMangager->attach('dispatch', new EventPlugin());
                    $dispatcher->setEventsManager($eventsMangager);
                    return $dispatcher;
                }
            );

            $di->setShared('response', function () {
                return new Response();
            });
        }
    }
}