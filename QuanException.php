<?php

/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2016/11/23
 * Time: 下午5:43
 */
namespace Quan\System;


class QuanException extends \Exception
{
    public function __construct($message = "", $code = 10011, $previous = null)
    {
        $code = $code > 10011 ? $code : 10011;
        parent::__construct($message, $code, $previous);
    }
}