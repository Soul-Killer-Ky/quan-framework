<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/2/5
 * Time: 上午11:32
 */
namespace Quan\System\Mvc\Struct;

trait SortedSets
{
    // TODO:可变变量
    protected static function zAdd($key, $score1, $value1, ...$param)
    {
        return static::process(__FUNCTION__, array_merge([$key, $score1, $value1, ], $param));
    }

    protected static function zRange($key, $start, $end, $withscores = null)
    {
        return static::process(__FUNCTION__, [$key, $start, $end, $withscores]);
    }

    protected static function zRem($key, $member1, $member2 = null, $memberN = null)
    {
        return static::process(__FUNCTION__, [$key, $member1, $member2, $memberN]);
    }

    protected static function zDelete($key, $member1, $member2 = null, $memberN = null)
    {
        return static::process(__FUNCTION__, [$key, $member1, $member2, $memberN]);
    }

    protected static function zRevRange($key, $start, $end, $withscore = null)
    {
        return static::process(__FUNCTION__, [$key, $start, $end, $withscore]);
    }

    protected static function zRangeByScore($key, $start, $end, array $options = array())
    {
        return static::process(__FUNCTION__, [$key, $start, $end, $options]);
    }

    protected static function zRevRangeByScore($key, $start, $end, array $options = array())
    {
        return static::process(__FUNCTION__, [$key, $start, $end, $options]);
    }

    protected static function zRangeByLex($key, $min, $max, $offset = null, $limit = null)
    {
        return static::process(__FUNCTION__, [$key,  $min, $max, $offset, $limit]);
    }

    protected static function zRevRangeByLex($key, $min, $max, $offset = null, $limit = null)
    {
        return static::process(__FUNCTION__, [$key,  $min, $max, $offset, $limit]);
    }

    protected static function zCount($key, $start, $end)
    {
        return static::process(__FUNCTION__, [$key, $start, $end]);
    }

    protected static function zRemRangeByScore($key, $start, $end)
    {
        return static::process(__FUNCTION__, [$key, $start, $end]);
    }

    protected static function zDeleteRangeByScore($key, $start, $end)
    {
        return static::process(__FUNCTION__, [$key, $start, $end]);
    }

    protected static function zRemRangeByRank($key, $start, $end)
    {
        return static::process(__FUNCTION__, [$key, $start, $end]);
    }

    protected static function zDeleteRangeByRank($key, $start, $end)
    {
        return static::process(__FUNCTION__, [$key, $start, $end]);
    }

    protected static function zCard($key)
    {
        return static::process(__FUNCTION__, [$key]);
    }

    protected static function zScore($key, $member)
    {
        return static::process(__FUNCTION__, [$key, $member]);
    }

    protected static function zRank($key, $member)
    {
        return static::process(__FUNCTION__, [$key, $member]);
    }

    protected static function zRevRank($key, $member)
    {
        return static::process(__FUNCTION__, [$key, $member]);
    }

    protected static function zIncrBy($key, $value, $member)
    {
        return static::process(__FUNCTION__, [$key, $value, $member]);
    }

    protected static function zUnion($Output, $ZSetKeys, array $Weights = null, $aggregateFunction = 'SUM')
    {
        return static::process(__FUNCTION__, [$Output, $ZSetKeys, $Weights, $aggregateFunction]);
    }

    protected static function zInter($Output, $ZSetKeys, array $Weights = null, $aggregateFunction = 'SUM')
    {
        return static::process(__FUNCTION__, [$Output, $ZSetKeys, $Weights, $aggregateFunction]);
    }
}