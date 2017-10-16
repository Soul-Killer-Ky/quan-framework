<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/2/5
 * Time: 上午10:13
 */
namespace Quan\System\Mvc;

interface StructInterface
{
    public function getId();

    public function setId($id);

    public function getReversedAttributes();

    public function getConnection();

    public function setConnectionService($connectionService = '');

    public function getConnectionService();

    public function fireEvent($eventName);

    public function fireEventCancel($eventName);

    public function save($key, $value = null, $ttl = 0);

    public function delete($key);

    public function flush();

    public static function process($method, array $args = []);
}