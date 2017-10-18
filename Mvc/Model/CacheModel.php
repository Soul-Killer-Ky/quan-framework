<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2016/12/8
 * Time: 下午12:21
 */
namespace Quan\System\Mvc\Model;


trait CacheModel
{

    protected static function _createKey($parameters)
    {
        $uniqueKey = [];

        foreach ($parameters as $key => $value) {
            if (is_scalar($value)) {
                $uniqueKey[] = $key . ":" . $value;
            } elseif (is_array($value)) {
                $uniqueKey[] = $key . ":[" . self::_createKey($value) . "]";
            }
        }

        return md5(join(",", $uniqueKey));
    }

    /***
     * @param mixed $parameters
     * @return \Phalcon\Mvc\Model\ResultsetInterface;
     */
    public static function find($parameters = null, $cache = true)
    {
        if (!is_array($parameters)) {
            $parameters = [$parameters];
        }

        if (!isset($parameters["cache"]) && true === $cache) {
            $parameters["cache"] = [
                "key"      => self::_createKey($parameters),
                "lifetime" => isset(self::$_queryCacheTime) ? self::$_queryCacheTime: 3,
            ];
        }

        return parent::find($parameters);
    }


    /****
     * @param string|array $parameters
     * @return static
     */
    public static function findFirst($parameters = null, $cache = true)
    {
        if (!is_array($parameters)) {
            $parameters = [$parameters];
        }

        if (!isset($parameters["cache"]) && true === $cache) {
            $parameters["cache"] = [
                "key"      => self::_createKey($parameters),
                "lifetime" => isset(self::$_queryCacheTime) ? self::$_queryCacheTime: 3,
            ];
        }

        if (0 === $parameters["cache"]['lifetime'] || '0' === $parameters["cache"]['lifetime']) {
            unset($parameters["cache"]['lifetime']);
        }

        return parent::findFirst($parameters);
    }
}