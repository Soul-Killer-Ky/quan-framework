<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/2/5
 * Time: 上午11:32
 */
namespace Quan\System\Mvc\Struct;

trait Lists
{
    protected static function lPop($key)
    {
        return static::process(__FUNCTION__, [$key]);
    }

    protected static function rPop($key)
    {
        return static::process(__FUNCTION__, [$key]);
    }

    protected static function blPop(array $keys, $timeout)
    {
        return static::process(__FUNCTION__, [$keys, $timeout]);
    }

    protected static function rPushx($key, $value)
    {
        return static::process(__FUNCTION__, [$key, $value]);
    }

    protected static function lPushx($key, $value)
    {
        return static::process(__FUNCTION__, [$key, $value]);
    }

    protected static function rPush($key, $value1, ... $valueN)
    {
        return static::process(__FUNCTION__, array_merge([$key, $value1, ], $valueN));
    }

    protected static function lPush($key, $value1, ...$valueN )
    {
        return static::process(__FUNCTION__, array_merge([$key, $value1, ], $valueN));
    }

    protected static function lLen($key)
    {
        return static::process(__FUNCTION__, [$key]);
    }

    protected static function lSize($key)
    {
        return static::process(__FUNCTION__, [$key]);
    }

    protected static function lIndex($key, $index)
    {
        return static::process(__FUNCTION__, [$key, $index]);
    }

    protected static function lSet($key, $index, $value)
    {
        return static::process(__FUNCTION__, [$key, $index, $value]);
    }

    protected static function lRange($key, $start, $end)
    {
        return static::process(__FUNCTION__, [$key, $start, $end]);
    }

    protected static function lTrim($key, $start, $stop)
    {
        return static::process(__FUNCTION__, [$key, $start, $stop]);
    }

    protected static function lRem($key, $value, $count)
    {
        return static::process(__FUNCTION__, [$key, $value, $count]);
    }

    protected static function lInsert($key, $position, $pivot, $value)
    {
        return static::process(__FUNCTION__, [$key, $position, $pivot, $value]);

    }
}