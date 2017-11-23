<?php

/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/11/23
 * Time: 下午5:43
 */
namespace Quan\System\Mvc\Model\Query;

class Builder extends \Phalcon\Mvc\Model\Query\Builder
{
    public function getQuery()
    {
        $phql = $this->getPhql();

        $dependencyInjector = $this->_dependencyInjector;

        if (!is_object($dependencyInjector)) {
            throw new \Exception("A dependency injection object is required to access ORM services");
        }

        $query = $dependencyInjector->get(
            "Quan\\System\\Mvc\\Model\\Query",
            [$phql, $dependencyInjector]
        );

        $bindParams = $this->_bindParams;
        if (is_array($bindParams)) {
            $query->setBindParams($bindParams);
        }

        $bindTypes = $this->_bindTypes;
		if (is_array($bindTypes)) {
            $query->setBindTypes($bindTypes);
		}

        if (is_bool($this->_sharedLock)) {
            $query->setSharedLock($this->_sharedLock);
		}

		return $query;
    }
}