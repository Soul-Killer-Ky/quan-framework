<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/2/5
 * Time: 上午10:58
 */
namespace Quan\System\Mvc\Struct;

trait Strings
{
    protected static function append($key, $value)
    {
        return static::process(__FUNCTION__, [$key, $value]);
    }

    protected static function get($key)
    {
        return static::process(__FUNCTION__, [$key]);
    }

    protected static function set($key, $value, $timeout = 0)
    {
        return static::process(__FUNCTION__, [$key, $value, $timeout]);
    }

    protected static function setex($key, $ttl, $value)
    {
        return static::process(__FUNCTION__, [$key, $ttl, $value]);
    }

    protected static function setnx($key, $value)
    {
        return static::process(__FUNCTION__, [$key, $value]);
    }

    protected static function incr($key)
    {
        return static::process(__FUNCTION__, [$key]);
    }

    protected static function incrByFloat($key, $increment)
    {
        return static::process(__FUNCTION__, [$key, $increment]);
    }

    protected static function incrBy($key, $value)
    {
        return static::process(__FUNCTION__, [$key, $value]);
    }

    protected static function decr($key)
    {
        return static::process(__FUNCTION__, [$key]);
    }

    protected static function decrBy($key, $value)
    {
        return static::process(__FUNCTION__, [$key, $value]);
    }

    protected static function mset(array $array)
    {
        return static::process(__FUNCTION__, [$array]);
    }

    protected static function msetnx(array $array)
    {
        return static::process(__FUNCTION__, [$array]);
    }

    protected static function setBit($key, $offset, $value)
    {
        return static::process(__FUNCTION__, [$key, $offset, $value]);
    }

    protected static function getBit($key, $offset)
    {
        return static::process(__FUNCTION__, [$key, $offset]);
    }

    protected static function bitpos($key, $bit, $start = 0, $end = null)
    {
        return static::process(__FUNCTION__, [$key, $bit, $start, $end]);
    }

    protected static function bitCount($key)
    {
        return static::process(__FUNCTION__, [$key]);
    }

    protected static function strlen($key)
    {
        return static::process(__FUNCTION__, [$key]);
    }

    protected static function getSet($key, $value)
    {
        return static::process(__FUNCTION__, [$key, $value]);
    }

    protected static function mGet($key)
    {
        return static::process(__FUNCTION__, [$key]);
    }
}