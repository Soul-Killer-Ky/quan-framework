<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/19
 * Time: 下午9:37
 */
namespace Quan\System\Mvc;

use Phalcon\Mvc\View\Engine\Php;
use Phalcon\Mvc\View\Engine\Volt;

class View extends \Phalcon\Mvc\View
{
    /**
     * @return Volt;
     */
    public function getVoltEngine()
    {
        return $this->getRegisteredEngines()['.volt'];
    }

    /**
     * @return Php
     */
    public function getPhtmlEngine()
    {
        return $this->getRegisteredEngines()['.phtml'];
    }
}

