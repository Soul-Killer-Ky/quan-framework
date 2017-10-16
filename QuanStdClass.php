<?php

/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2016/11/25
 * Time: 下午5:56
 *
 * 标准类，用于处理空值的容错处理，
 */
namespace Quan\System;

class QuanStdClass extends \stdClass
{
    public function __call($name, $arguments)
    {
        return;
    }
}