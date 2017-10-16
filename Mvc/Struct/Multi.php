<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/3/30
 * Time: 上午10:01
 */

namespace Quan\System\Mvc\Struct;


trait Multi
{
    /**
     * @param int $type \Redis::MULTI|\Redis::PIPELINE
     * @return \Redis
     */
    protected static function Multi($type = \Redis::PIPELINE)
    {
        return static::process(__FUNCTION__, [$type]);
    }

    /**
     * @param int $type \Redis::MULTI|\Redis::PIPELINE
     * @return \Redis
     */
    protected static function MultiByHost($type = null, $host = null)
    {
        $type = $type ? : \Redis::PIPELINE;
        if ($host) {
            return static::process('Multi', [$host, $type]);
        } else {
            return static::process('Multi', [$type]);
        }
    }

    /**
     * @param int $type \Redis::MULTI|\Redis::PIPELINE
     * @return \Redis
     */
    protected static function Target($key)
    {
        return static::process('_target', [$key]);
    }
}