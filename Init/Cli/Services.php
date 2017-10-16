<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2016/11/22
 * Time: 下午5:09
 */

namespace Quan\System\Init\Cli;

use Phalcon\Cli\Router as CliRouter;

class Services extends \Quan\System\Services
{
    protected function bindServices()
    {
        $this->setShared('router', function () {
            $router = new CliRouter();
            $router->setDefaultModule($this->get('config')['setting']->application->default_module);
            return $router;
        });
    }
}