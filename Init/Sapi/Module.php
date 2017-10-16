<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2016/11/22
 * Time: 下午6:33
 */

namespace Quan\System\Init\Sapi;
use Phalcon\Loader;
use Phalcon\DiInterface;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Cli\Dispatcher as CliDispatcher;
use Phalcon\Events\Manager as EventsManage;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Text;
use Quan\System\EventPlugin;
use Quan\System\QuanStdClass;
use Quan\System\Response;

class Module implements ModuleDefinitionInterface
{
    /**
     * 注册自定义加载器
     */
    public function registerAutoloaders(DiInterface $di = null)
    {
        $loader = new Loader();

        if ($di) {
            $module = $di->get('router')->getModuleName();
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
    }


    /**
     * 注册自定义服务
     */
    public function registerServices(DiInterface $di)
    {
        $settings = $appconfig = $di->get('config')['setting']->application;
        $module = $di->get('router')->getModuleName();
        $modulesNoView = array_unique(array_filter(array_map('trim', explode(',', $settings->noview_module))));

        $di->setShared(
            "dispatcher",
            function () use ($settings, $module) {
                $dispatcher = new Dispatcher();
                $namespace= implode('\\', [ucfirst($settings->namespace_root), ucfirst($module), 'Controllers']);
                $dispatcher->setDefaultNamespace($namespace. '\\');
                $dispatcher->setDefaultController('index');
                $dispatcher->setDefaultAction('index');
                $dispatcher->setActionSuffix('');

                $eventsMangager = new EventsManage();
                $eventsMangager->attach('dispatch', new EventPlugin());
                $dispatcher->setEventsManager($eventsMangager);
                return $dispatcher;
            }
        );

        $di->setShared(
            'response',
            function () use ($settings, $module) {
                $response = new Response();
                $response->setVersion($settings->current_version);
                return $response;
            }
        );

        if (!in_array($module, $modulesNoView)) {
            $di->setShared(
                "view",
                function () use ($appconfig, $module) {
                    $view = new View();
                    $view->registerEngines([
                        ".volt" => function ($view, $di) use ($module) {
                            $volt = new Volt($view, $di);
                            $volt->setOptions(
                                [
                                    "compiledExtension" => ".compiled",
                                    "compiledPath" => function ($templatePath) use ($module){
                                        $dirName = implode(DIRECTORY_SEPARATOR, [RUNTIME_PATH, $module, 'compiled-templates']);
                                        $dirName = Text::reduceSlashes($dirName);
                                        if (!is_dir($dirName)) {
                                            mkdir($dirName, 0755, true);
                                        }
                                        return $dirName. DIRECTORY_SEPARATOR. basename($templatePath). '.php';
                                    }
                                ]
                            );
                            return $volt;
                        },
                        ".phtml"   => "Phalcon\\Mvc\\View\\Engine\\Php",
                    ]);
                    $view->setViewsDir(sprintf(APP_PATH. '/applications/%s/%s/', $module, 'views'));
                    return $view;
                }
            );
        } else {
            $di->setShared('view', new QuanStdClass());
        }
    }
}