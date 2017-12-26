<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/12/26
 * Time: 下午2:48
 */
namespace Quan\System\Db\Adapter\Pdo;

use Quan\System\Db\Dialect\Mysql as MysqlDielet;

class Mysql extends \Phalcon\Db\Adapter\Pdo\Mysql
{
    public function __construct(array $descriptor)
    {
        parent::__construct($descriptor);
        $this->_dialect = new MysqlDielet();
    }

    public function query($sqlStatement, $bindParams = null, $bindTypes = null)
    {
        return parent::query($sqlStatement, $bindParams, $bindTypes);
    }
}