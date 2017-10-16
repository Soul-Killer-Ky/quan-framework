<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/2/5
 * Time: 上午11:33
 */
namespace Quan\System\Mvc\Struct;

trait Hashes
{
    protected static function hSet($key, $hashKey, $value)
    {
        return static::process(__FUNCTION__, [$key, $hashKey, $value]);
    }

    protected static function hSetNx($key, $hashKey, $value)
    {
        return static::process(__FUNCTION__, [$key, $hashKey, $value]);
    }

    protected static function hGet($key, $hashKey)
    {
        return static::process(__FUNCTION__, [$key, $hashKey]);
    }

    protected static function hLen($key)
    {
        return static::process(__FUNCTION__, [$key]);
    }

    protected static function hDel($key, $hashKey1, ... $hashKeyN)
    {
        return static::process(__FUNCTION__, array([$key, $hashKey1], $hashKeyN));
    }

    protected static function hKeys($key)
    {
        return static::process(__FUNCTION__, [$key]);
    }

    protected static function hVals($key)
    {
        return static::process(__FUNCTION__, [$key]);
    }

    protected static function hGetAll($key)
    {
        return static::process(__FUNCTION__, [$key]);
    }

    protected static function hExists($key, $hashKey)
    {
        return static::process(__FUNCTION__, [$key, $hashKey]);
    }

    protected static function hIncrBy($key, $hashKey, $value)
    {
        return static::process(__FUNCTION__, [$key, $hashKey, $value]);
    }

    protected static function hIncrByFloat($key, $field, $increment)
    {
        return static::process(__FUNCTION__, [$key, $field, $increment]);
    }

    protected static function hMset($key, $hashKeys)
    {
        return static::process(__FUNCTION__, [$key, $hashKeys]);
    }

    protected static function hMGet($key, $hashKeys)
    {
        return static::process(__FUNCTION__, [$key, $hashKeys]);
    }

}