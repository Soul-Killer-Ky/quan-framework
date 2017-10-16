<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2016/11/22
 * Time: 下午5:09
 *          .,:,,,                                        .::,,,::.
 *        .::::,,;;,                                  .,;;:,,....:i:
 *        :i,.::::,;i:.      ....,,:::::::::,....   .;i:,.  ......;i.
 *        :;..:::;::::i;,,:::;:,,,,,,,,,,..,.,,:::iri:. .,:irsr:,.;i.
 *        ;;..,::::;;;;ri,,,.                    ..,,:;s1s1ssrr;,.;r,
 *        :;. ,::;ii;:,     . ...................     .;iirri;;;,,;i,
 *        ,i. .;ri:.   ... ............................  .,,:;:,,,;i:
 *        :s,.;r:... ....................................... .::;::s;
 *        ,1r::. .............,,,.,,:,,........................,;iir;
 *        ,s;...........     ..::.,;:,,.          ...............,;1s
 *       :i,..,.              .,:,,::,.          .......... .......;1,
 *      ir,....:rrssr;:,       ,,.,::.     .r5S9989398G95hr;. ....,.:s,
 *     ;r,..,s9855513XHAG3i   .,,,,,,,.  ,S931,.,,.;s;s&BHHA8s.,..,..:r:
 *    :r;..rGGh,  :SAG;;G@BS:.,,,,,,,,,.r83:      hHH1sXMBHHHM3..,,,,.ir.
 *   ,si,.1GS,   sBMAAX&MBMB5,,,,,,:,,.:&8       3@HXHBMBHBBH#X,.,,,,,,rr
 *   ;1:,,SH:   .A@&&B#&8H#BS,,,,,,,,,.,5XS,     3@MHABM&59M#As..,,,,:,is,
 *  .rr,,,;9&1   hBHHBB&8AMGr,,,,,,,,,,,:h&&9s;   r9&BMHBHMB9:  . .,,,,;ri.
 *  :1:....:5&XSi;r8BMBHHA9r:,......,,,,:ii19GG88899XHHH&GSr.      ...,:rs.
 *  ;s.     .:sS8G8GG889hi.        ....,,:;:,.:irssrriii:,.        ...,,i1,
 *  ;1,         ..,....,,isssi;,        .,,.                      ....,.i1,
 *  ;h:               i9HHBMBBHAX9:         .                     ...,,,rs,
 *  ,1i..            :A#MBBBBMHB##s                             ....,,,;si.
 *  .r1,..        ,..;3BMBBBHBB#Bh.     ..                    ....,,,,,i1;
 *   :h;..       .,..;,1XBMMMMBXs,.,, .. :: ,.               ....,,,,,,ss.
 *    ih: ..    .;;;, ;;:s58A3i,..    ,. ,.:,,.             ...,,,,,:,s1,
 *    .s1,....   .,;sh,  ,iSAXs;.    ,.  ,,.i85            ...,,,,,,:i1;
 *     .rh: ...     rXG9XBBM#M#MHAX3hss13&&HHXr         .....,,,,,,,ih;
 *      .s5: .....    i598X&&A&AAAAAA&XG851r:       ........,,,,:,,sh;
 *      . ihr, ...  .         ..                    ........,,,,,;11:.
 *         ,s1i. ...  ..,,,..,,,.,,.,,.,..       ........,,.,,.;s5i.
 *          .:s1r,......................       ..............;shs,
 *          . .:shr:.  ....                 ..............,ishs.
 *              .,issr;,... ...........................,is1s;.
 *                 .,is1si;:,....................,:;ir1sr;,
 *                    ..:isssssrrii;::::::;;iirsssssr;:..
 *                         .,::iiirsssssssssrri;;:.
 */
namespace Quan\System;

use Phalcon\Mvc\Url;
use Quan\System\Cache\Backend\Libmemcached;
use Phalcon\Cache\Frontend\Data as FrontendData;
use Phalcon\Cli\Router as CliRouter;
use Phalcon\Config;
use Phalcon\Db\Adapter as DbAdapter;
use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;
use Phalcon\Db\Profiler as ProfilerDb;
use Phalcon\Di;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Filter;
use Phalcon\Http\Request;
use Phalcon\Logger\Adapter\File as FileLogger;
use Phalcon\Logger\Adapter\Stream as StreamLogger;
use Phalcon\Logger\Adapter\Syslog as Syslogger;
use Phalcon\Mvc\Model\MetaData\Files as FilesMetaData;
use Phalcon\Mvc\Model\MetaData\Strategy\Introspection as StrategryIntrospection;
use Phalcon\Session\Adapter\Files as Session;
use Quan\System\Mvc\Struct\Manager as StructsManager;
use Quan\System\Queue\Rabbitmq;


class Services extends Di
{
    private $_sapi = '';

    private $_logpath = '';

    public function __construct($sapi = '')
    {
        parent::__construct();

        $this->_sapi = $sapi;
        $this->setShared('config', function () {
            return new \Quan\System\Config(COMMON_PATH. 'config/', $this);
        });

        $this->init();
        $this->bindServices();
    }

    protected function bindServices()
    {}

    private function init()
    {
        $this->initRequestAndResponse();
        $this->initRunTime();
        $this->initErrorHandle();
        $this->initException();
        $this->initLog();
        $this->initDb();
        $this->initCache();
        $this->initFilter();
        $this->initSession();
        $this->initRabbitmq();
        $this->initEscaper();
        $this->initUrl();
    }

    public function getModules()
    {
        $config = $this->get('config')['setting'];
        $modules = [];

        foreach (explode(',', $config->application->modules) as $module) {
            $modules[$module] =
                [
                    'className' => '\\'. implode('\\', ['Quan', 'System', 'Init', ucfirst($this->_sapi), 'Module']),
                    'path' => SYSTEM_PATH. '/Init/'. ucfirst($this->_sapi). '/Module.php'
                ];
        }
        return $modules;
    }


    public function initException()
    {
        if (PHP_SAPI !== 'cli') {
            set_error_handler(array($this, '_initErrorHandle'));
            set_exception_handler(array($this, '_initExceptionHandle'));
        }
    }

    public function initErrorHandle() {
        register_shutdown_function(array($this, '_initException'));
    }

    public function initRunTime()
    {

    }

    public function initDb()
    {
        $this->set("profiler", function () {
                return new ProfilerDb();
            },
            true
        );

        $config = $this->get('config');

        $dbconfig = $config['db'];
        $appconfig = $config['setting'];
        $memcacheconfig = isset($config['memcache']) ? $config['memcache']: null;
        $dbconfig['db'] = $dbconfig['default'] ? : $dbconfig['default'];

        foreach ($dbconfig as $key => $item) {
            $conn = $key == 'db' ? 'db' : 'db_'. $key;
            $this->set($conn,  function ($event = null, $connection = null) use ($item, $conn, $key, $appconfig) {

                /** @var EventsManager $eventsManager */
                /** @var ProfilerDb $profiler */
                /** @var Logger $logger */
                $eventsManager = new EventsManager();
                $profiler = $this->getProfiler();
                $logger = $this->get('log');

                if ($appconfig->get('database', null) &&  $appconfig['database']->get('sql_profiling_enable', 0) == 1) {
                    $filename1 = $logger->setLogPath(sprintf('sql_%s.log', date('Ymd')));
                    $filename2 = $logger->setLogPath(sprintf('sql_error_%s.log', date('Ymd')));
                    $logger1 = new FileLogger($filename1);
                    $logger2 = new FileLogger($filename2);
                } else {
                    $logger1 = $logger2 = null;
                }

                $eventsManager->attach('db',
                    function (Event $event, DbAdapter $connection) use ($profiler, $logger1, $logger2) {
                        if ($event->getType() === "beforeQuery") {
                            $profiler->startProfile(
                                $connection->getRealSQLStatement(),
                                $connection->getSqlVariables(),
                                $connection->getSQLBindTypes()
                            );

                            if ($logger1 instanceof FileLogger) {
                                $logger1->info(serialize($connection->getSqlVariables()));
                                $logger1->info($connection->getRealSQLStatement());
                            }
                        }

                        if ($event->getType() === "afterQuery") {
                            $profiler->stopProfile();
                            if ($logger1 instanceof FileLogger) {
                                $logger1->info("Total Elapsed Time:". $profiler->getTotalElapsedSeconds(). PHP_EOL);
                            }

                            if ($logger2 instanceof FileLogger) {
                                $error = $connection->getErrorInfo();
                                if ($error[0] != '00000') {
                                    $logger2->error(implode(PHP_EOL, $error));
                                }
                            }
                        }
                    }
                );

                $item = $item->toArray();
                $item['options'] = [
                    \Pdo::MYSQL_ATTR_INIT_COMMAND => "set names '{$item['charset']}';",
                ];
                $db = new PdoMysql($item);
                $db->query('SET session wait_timeout=28800', false);
                $db->query('SET session interactive_timeout=28800', false);
                $db->setEventsManager($eventsManager);
                return $db;
            });
        }

        $this->setShared('modelsManager', function () {
            return new ModelsManager();
        });

        // 模型元数据
        if ($appconfig->get('database', null) &&  $appconfig['database']->get('meta_data_cache', 0) == 1) {
            $this->setShared('modelsMetadata', function () {

                $module = trim($this->getRouter()->getModuleName());

                $metadataDir = RUNTIME_PATH. $module. implode(DIRECTORY_SEPARATOR, ['', 'cache', 'metadata', '']);
                if (!is_dir($metadataDir)) {
                    mkdir($metadataDir, ENVIROMENT == 'production' ? 0755: 0777, true);
                }

                $metadata = new FilesMetaData([
                    'metaDataDir' => $metadataDir,
                    "lifetime" => 86400,
                    "prefix"   => "my-prefix",
                ]);

                $metadata->setStrategy(new StrategryIntrospection());

                return $metadata;
            });
        }

        // 数据库查询缓存
        if (extension_loaded('memcached') && $appconfig['database']->get('query_cache', 0) == 1) {

            $this->set('modelsCache', function () use ($memcacheconfig) {

                $servers = $memcacheconfig;

                if ($servers instanceof Config) {
                    $servers = array_filter($servers->toArray());
                    foreach ($servers as &$server) {
                        $server = array_filter($server, function ($v) { return in_array($v, ['host', 'weight', 'port'] );}, ARRAY_FILTER_USE_KEY);
                    }
                }

                $frontend = new FrontendData($servers[0]['key_timelife'] ? : 3);

                $cache = new Libmemcached($frontend,  [
                    'servers' => $servers,
                    'client' => [
                        \Memcached::OPT_HASH => \Memcached::HASH_MD5,
                        \Memcached::OPT_PREFIX_KEY => 'sqlcache.',
                        \Memcached::OPT_LIBKETAMA_COMPATIBLE => true,
                    ],
                    'persistent_id' => null,
                ]);

                return $cache;
            });
        }
    }

    public function initCache()
    {

        $this->setShared('structsManager', function () {
            return new StructsManager();
        });

        $this->setShared('struct', 'Quan\\System\\Cache\\Adapter\\Redis');
    }

    public function initLog()
    {
        $config = $this->get('config')['setting'];
        $config = $config->get('log', ['log_enable' => 0, 'log_type' => 'file']);

        $this->set('log', function () use ($config) {
            $logger = new Logger();
            $logger->setDI($this);
            return $logger;
        });
    }

    public function initSession()
    {
        $this->setShared(
            "session",
            function () {
                $session = new Session();
                $session->start();
                return $session;
            }
        );
    }

    public function initRequestAndResponse()
    {
        $this->setShared('request', function () {
            return new Request();
        });

        $this->setShared('response', function () {
            return new Response();
        });
    }

    public function initFilter()
    {
        $this->setShared('filter', function () {
            return new Filter();
        });
    }

    public function initRabbitmq()
    {
        $this->setShared('rabbitmq', function () {
            return new Rabbitmq($item = 'default', $this);
        });
    }

    public function initEscaper()
    {
        $this->set('escaper', function () {
            return new \Phalcon\Escaper();
        });
    }

    public function initUrl()
    {
        $this->setShared('url', function () {
            return new Url();
        });
    }


    public function _initException()
    {
        $e = error_get_last();
        if ($e !== null) {
//            var_dump($e);
//            exit(-1);
        }
    }

    public function _initErrorHandle($errno = 0, $errstr = '', $errfile = '', $errline = null)
    {
        // 写入日志
    }

    /**
     * @internal Response $response;
     * @param \Throwable|\Exception $exception
     * @internal param \Throwable $e
     */
    public function _initExceptionHandle($exception)
    {
        /** @var Response $response */
        $response = $this->get('response');

        if (defined('APP_DEBUG') && APP_DEBUG === true) {
            $data = [
                'mu' => round(memory_get_usage() / 1024 /1024, 5),
                'mpu' => round(memory_get_peak_usage() / 1024 /1024, 5),
                'tc' =>  round(microtime(true) - APP_STARTTIME, 6),
            ];

            $data['file'] = $exception->getFile(). '('. $exception->getLine(). ')';
            $data['trace'] = explode(PHP_EOL, $exception->getTraceAsString());

            $message = sprintf('%s: %s', get_class($exception), $exception->getMessage());
        } else {
            $data = [];
            $message = $exception->getMessage();
        }

        $code = $exception instanceof QuanException ? $exception->getCode() : 10011;
        header('Api-Error: ' . $exception->getCode(), false, 500);
        $response->jsonException($code, $message, $data);
        $response->send();
    }
}

