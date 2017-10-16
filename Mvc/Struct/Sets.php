<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/2/5
 * Time: 上午11:32
 */
namespace Quan\System\Mvc\Struct;

trait Sets
{
    protected static function setRange($key, $offset, $value)
    {
        return static::process(__FUNCTION__, [$key, $offset, $value]);
    }

    protected static function sAdd($key, $value1, ...$valueN)
    {
        return static::process(__FUNCTION__, array_merge([$key, $value1], $valueN));
    }

    protected static function sAddArray($key, array $values)
    {
        return static::process(__FUNCTION__, [$key, $values]);
    }

    protected static function sRem($key, $member1, ...$members)
    {
        return static::process(__FUNCTION__, array_merge([$key, $member1], $members));
    }

    protected static function sMove($srcKey, $dstKey, $member)
    {
        return static::process(__FUNCTION__, [$srcKey, $dstKey, $member]);
    }

    protected static function sIsMember($key, $value)
    {
        return static::process(__FUNCTION__, [$key, $value]);
    }

    protected static function sCard($key)
    {
        return static::process(__FUNCTION__, [$key]);
    }

    protected static function sPop($key)
    {
        return static::process(__FUNCTION__, [$key]);
    }

    protected static function sRandMember($key, $count = null)
    {
        return static::process(__FUNCTION__, [$key, $count]);
    }

    protected static function sInter($key1, $key2, ...$keyN)
    {
        return static::process(__FUNCTION__, array_merge([$key1, $key2, ], $keyN));
    }

    protected static function sInterStore($dstKey, $key1, $key2, ...$keyN)
    {
        return static::process(__FUNCTION__, array_merge([$dstKey, $key1, $key2], $keyN));
    }

    protected static function sUnion($key1, $key2, ...$keyN)
    {
        return static::process(__FUNCTION__, array_merge([$key1, $key2, ], $keyN));
    }

    protected static function sUnionStore($dstKey, $key1, $key2, ...$keyN )
    {
        return static::process(__FUNCTION__, array_merge([$dstKey, $key1, $key2, ], $keyN));
    }

}