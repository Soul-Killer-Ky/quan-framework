<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/2/5
 * Time: 上午11:06
 */
namespace Quan\System\Mvc\Struct;

use Quan\System\Mvc\StructInterface;

interface ManagerInterface
{
    public function initialize(StructInterface $struct);

    public function isInitialized($modelName);

    public function getLastInitialized();

    public function setCustomEventsManager(StructInterface $struct, \Phalcon\Events\ManagerInterface $eventManager);

    public function getCustomEventsManager(StructInterface $struct);

    public function setConnectionService(StructInterface $struct, $connectionService = '');

    public function getConnectionService(StructInterface $struct);

    public function getConnection(StructInterface $struct);

    public function notifyEvent($eventName, StructInterface $struct);
}

