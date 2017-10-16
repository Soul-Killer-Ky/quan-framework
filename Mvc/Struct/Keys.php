<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/2/5
 * Time: 上午11:33
 */
namespace Quan\System\Mvc\Struct;

trait Keys
{
    protected static function keys($partten = '*')
    {
       return static::process(__FUNCTION__, [$partten]);
    }

    protected static function ttl($key)
    {
        return static::process(__FUNCTION__, [$key]);
    }

    protected static function pttl($key)
    {
        return static::process(__FUNCTION__, [$key]);
    }

    protected static function exists($key)
    {
        return static::process(__FUNCTION__, [$key]);
    }

    protected static function expire($key, $ttl = 0)
    {
        return static::process(__FUNCTION__, [$key, $ttl]);
    }

    protected static function expireAt($key, $timestamp = 0)
    {
        return static::process(__FUNCTION__, [$key, $timestamp]);
    }

    protected static function rename($srcKey, $dstKey)
    {
        return static::process(__FUNCTION__, [$srcKey, $dstKey]);
    }

    protected static function renamenx($srcKey, $dstKey)
    {
        return static::process(__FUNCTION__, [$srcKey, $dstKey]);
    }

    protected static function type($key)
    {
        return static::process(__FUNCTION__, [$key]);
    }

    protected static function del(... $keys)
    {
        return static::process(__FUNCTION__, $keys);
    }
}