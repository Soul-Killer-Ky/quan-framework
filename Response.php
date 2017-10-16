<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2016/11/23
 * Time: 下午12:02
 *
 * 响应类
 */
namespace Quan\System;

class Response extends \Phalcon\Http\Response
{
    protected $version;

    protected $willReturnJson = false;

    /***
     * 发送 json
     * @param $code
     * @param $message
     * @param $data
     */
    public function json($code, $message, array $data = [])
    {
        $this->setJson(['code' => intval($code), 'msg' => strval($message), 'data' => (object)($data)]);
    }

    /***
     * @param $data
     */
    public function setJson($data)
    {
        $this->setWillReturnJson(true);
        $this->setJsonContent($data, JSON_NUMERIC_CHECK);
        $this->setHeader('Content-type', 'application/vnd.quan.api+json;version='. $this->getVersion());
        $this->setHeader('X-Content-Type-Options', 'nosniff');
    }

    /***
     * @param $code
     * @param $message
     * @param array $data
     */
    public function jsonException($code, $message, array $data = [])
    {
        $this->setWillReturnJson(true);
        $this->setJsonContent(['code' => intval($code), 'msg' => strval($message), 'trace' => (object)($data)], JSON_NUMERIC_CHECK);
        $this->setHeader('Content-type', 'application/vnd.quan.api+json;version='. $this->getVersion());
        $this->setHeader('X-Content-Type-Options', 'nosniff');
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return mixed
     */
    public function getWillReturnJson()
    {
        return $this->willReturnJson;
    }

    /**
     * @param mixed $willReturnJson
     */
    protected function setWillReturnJson($willReturnJson)
    {
        $this->willReturnJson = $willReturnJson;
    }
}