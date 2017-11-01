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
use Quan\System\Mvc\Controller\Event as ControllerEvent;
use Quan\System\Mvc\View as QuanView;
use Quan\System\Mvc\View\Event as ViewEvent;
use Quan\System\QuanStdClass;
use Quan\System\Response;
use Quan\System\System;

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

				$eventsMangager = new EventsManage();
				$handler = ControllerEvent::instance($module, $settings);
				if (!$handler instanceof  ControllerEvent) {
					$eventsMangager->attach('dispatch',  new ControllerEvent());
				}
				$eventsMangager->attach('dispatch',  $handler);

				$dispatcher = new Dispatcher();
				$namespace= implode('\\', [ucfirst($settings->namespace_root), ucfirst($module), 'Controllers']);
				$dispatcher->setDefaultNamespace($namespace. '\\');
				$dispatcher->setDefaultController('index');
				$dispatcher->setDefaultAction('index');
				$dispatcher->setActionSuffix('');
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
				function () use ($appconfig, $module, $di) {

					$viewDir = sprintf(APP_PATH . '/applications/%s/%s/', $module, 'views');
					$eventManager = new EventsManage();
					$handler      = ViewEvent::instance($module, $appconfig);
					if (!$handler instanceof ViewEvent) {
						$eventManager->attach('view', new ViewEvent());
					}
					$eventManager->attach('view', $handler);

					$view = new QuanView();
					$view->setEventsManager($eventManager);
					$volt        = new Volt($view, $di);
					$voltOptions = [
						"compiledExtension" => ".compiled",
						"compiledPath"      => function ($templatePath) use ($module, $viewDir) {

							if (\Phalcon\Text::startsWith($templatePath, $viewDir)) {
								$relative = str_replace($viewDir, '', $templatePath);
							} else {
								$relative = '';
							}
							$runtimePath = implode(DIRECTORY_SEPARATOR, [RUNTIME_PATH, $module, 'complied-path']);
							$runtimePath .= '/'. $relative. '.php';

							$runtimeDir = dirname($runtimePath);
							if (!is_dir($runtimeDir)) {
								mkdir($runtimeDir, 0755, true);
							}
							return $runtimePath;
						}
					];

					if (ENVIROMENT == System::ENV_DEVELOPMENT) {
						$voltOptions['stat']          = true;
						$voltOptions['compileAlways'] = true;
					}
					$volt->setOptions($voltOptions);
					$compiler = $volt->getCompiler();
					$compiler->addExtension(
						new \Quan\System\Mvc\View\Extension()
					);

					$view->registerEngines([
						".volt"  => $volt,
						".phtml" => "Phalcon\\Mvc\\View\\Engine\\Php",
					]);
					$view->setViewsDir($viewDir);
					return $view;
				}
			);
		} else {
			$di->setShared('view', new QuanStdClass());
		}
	}
}