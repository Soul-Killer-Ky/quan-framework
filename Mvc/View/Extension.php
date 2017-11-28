<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/1
 * Time: 11:55
 */
namespace Quan\System\Mvc\View;

class Extension
{
	public function compileFunction($name, $arguments)
	{
		if (function_exists($name)) {
			return $name . "(" . $arguments . ")";
		}
	}
}