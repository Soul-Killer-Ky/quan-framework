<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2016/11/22
 * Time: 下午5:09
 */
namespace Quan\System\Init\Sapi;

use Phalcon\Mvc\Router;

class Services extends \Quan\System\Services
{
    protected function bindServices()
    {
        $this->setShared(
            'router',
            function () {
                $router = new Router();
                $router->setDefaultModule($this->get('config')['setting']->application->default_module);
                $router->removeExtraSlashes(true);
                $router->add('/:module/:controller/:action',         ['module'=> 1, 'controller' => 2, 'action' => 3]);
                $router->add('/:module/:controller/:action/:params', ['module'=> 1, 'controller' => 2, 'action' => 3, 'params' => 4]);
                return $router;
            }
        );
    }
}