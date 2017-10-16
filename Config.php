<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2016/11/22
 * Time: 下午5:41
 */
namespace Quan\System;

use ArrayAccess;
use Phalcon\Di;
use Phalcon\DiInterface;
use Phalcon\Cache\Backend\File as BackendFile;
use Phalcon\Cache\Frontend\Data as FrontendData;

class Config implements ArrayAccess, Di\InjectionAwareInterface
{
    protected $_dependencyInjetor = null;

    private $_path;

    private $_config;

    private $_args;

    private $_urls = [];

    public function __construct($path = '', DiInterface $di = null)
    {
        $this->_path = $path;
        if (!$di instanceof DiInterface) {
            $di = Di::getDefault();
        }
        $this->_dependencyInjetor = $di;
    }

    public function setDI(DiInterface $di)
    {
        if (!$di instanceof DiInterface) {
            $di = Di::getDefault();
        }
        $this->_dependencyInjetor = $di;
    }

    public function getDI()
    {
        return $this->_dependencyInjetor;
    }


    /**
     * 加载ini 配置
     * 环境配置复盖模版配置，按section覆盖，不进行递归覆盖， 所以不用 Config::merge() 方法，
     * @param $offset
     */
    private function __loadConfig($offset)
    {
        if (!$this->_args) {
            $this->_args = $this->args();
        }

        foreach (array('ini', 'yaml') as $ext) {

            $filepath1 = $this->_path. '/'. ENVIROMENT. '/'. $offset. '.'. $ext;
            $filepath2 = $this->_path.  '/'. $offset. '.'. $ext;

            $reflectionClass = new \ReflectionClass('Phalcon\\Config\\Adapter\\'. ucfirst($ext));

            if (!isset($this->_config[$offset])) {

                $isEnviromentFileExists = file_exists($filepath1);
                $isRootFileExists= file_exists($filepath2);

                if (true === $isEnviromentFileExists && true == $isRootFileExists) {
                    $config2 = $reflectionClass->newInstance($filepath2, $this->_args[$ext]);
                    $config1 = $reflectionClass->newInstance($filepath1, $this->_args[$ext]);
                    $this->_config[$offset] = new \Phalcon\Config($config1->toArray() + $config2->toArray());

                } elseif ($isEnviromentFileExists) {
                    $this->_config[$offset] = $reflectionClass->newInstance($filepath1, $this->_args[$ext]);

                } elseif ($isRootFileExists) {
                    $this->_config[$offset] = $reflectionClass->newInstance($filepath2, $this->_args[$ext]);
                }
            }

            if (isset($this->_config[$offset])) {
                break;
            }
        }
    }

    private function args()
    {
        $params = [
            'ini'  => INI_SCANNER_NORMAL,
            'yaml' => [
                '!PARSEDSN' => [$this, 'yamlParseDsn'],
            ],
        ];
        return $params;
    }

    public function yamlParseDsn($value)
    {
        $query = [];
        $dsn = strlen(getenv($value['system'])) > 0 ? getenv($value['system']) : $value['default'];
        $result =  parse_url($dsn);
        parse_str($result['query'], $query);
        $result = array_merge((array)$result, (array)$query);
        unset($result['query']);
        return $result;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        if (!isset($this->_config[$offset])){

            if (APP_DEBUG != true) {
                $cache = new BackendFile(new FrontendData(
                    [
                        "lifetime" => 172800,
                    ]
                ), [
                    "cacheDir" =>  $this->_checkRuntimePath(). '/',
                ]);

                $cacheKey = "{$offset}.cache";

                if (!$config = $cache->get($cacheKey)) {
                    $this->__loadConfig($offset);
                    $cache->save($cacheKey, $this->_config[$offset]);
                } else {
                    $this->_config[$offset] = $config;
                }
            } else {
                $this->__loadConfig($offset);
            }
        }
        return isset($this->_config[$offset]);
    }


    /**
     * @param mixed $offset
     * @return \Phalcon\Config[]
     * @throws QuanException
     */
    public function offsetGet($offset)
    {
        if (!isset($this->_config[$offset])){


            if (APP_DEBUG != true) {
                $cache = new BackendFile(new FrontendData(
                    [
                        "lifetime" => 172800,
                    ]
                ), [
                    "cacheDir" =>  $this->_checkRuntimePath(). '/',
                ]);

                $cacheKey = "{$offset}.cache";

                if (!$config = $cache->get($cacheKey)) {
                    $this->__loadConfig($offset);
                    $cache->save($cacheKey, $this->_config[$offset]);
                } else {
                    $this->_config[$offset] = $config;
                }
            } else {
                $this->__loadConfig($offset);
            }

            if (!isset($this->_config[$offset])){
                throw new QuanException(sprintf('Not found config `%s`', $offset));
            }
        }
        return $this->_config[$offset];
    }


    public function offsetSet($offset, $value)
    {
        $this->_config[$offset] = $value;
    }


    public function offsetUnset($offset)
    {
        unset($this->_config[$offset]);
    }

    private function _checkRuntimePath()
    {
        // @todo 加入编译缓存
        $dir = implode(DIRECTORY_SEPARATOR, [rtrim(RUNTIME_PATH, DIRECTORY_SEPARATOR), '_common', 'cache', 'config']);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * 直接通过file.section.key获取配置值
     * @param string $key
     * @param DiInterface $di
     * @return mixed
     */
    public static function get($key = '', DiInterface $di = null)
    {
        static $tmp = [];
        if (isset($tmp[$key])) {
            $oldValue = $tmp[$key];
        } else {
            list($file, $section, $key) = explode('.', $key, 3);
            $oldValue =  $di ? : Di::getDefault()->get('config')[$file]->$section->$key;
        }
        return $oldValue;
    }

    /**
     * 直接通过file.section.key获取配置值
     * @param string $key
     * @param null $value
     * @param DiInterface $di
     * @return bool
     */
    public static function set($key = '', $value = null, DiInterface $di = null)
    {
        list($file, $section, $key) = explode('.', $key, 3);
        if (!is_null($value)) {
            $di ? : Di::getDefault()->get('config')[$file]->$section->$key = $value;
        }
        return true;
    }

    /**
     * @param $key
     * @param DiInterface $di
     * @return array
     */
    public static function items($key, DiInterface $di = null)
    {
        static $tmp = [];
        if (isset($tmp[$key])) {
            $oldValue = $tmp[$key];
        } else {
            list($file, $section) = explode('.', $key, 2);
            $oldValue = $tmp[$key] = $di ? : Di::getDefault()->get('config')[$file]->get($section, null);
        }
        return $oldValue ? $oldValue->toArray() : array();
    }
}