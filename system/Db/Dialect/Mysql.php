<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/12/26
 * Time: 下午3:16
 */
namespace Quan\System\Db\Dialect;

class Mysql extends \Phalcon\Db\Dialect\Mysql
{
    public function select(array $definition)
    {
        return parent::select($definition); // TODO: Change the autogenerated stub
    }
}