<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2016/11/24
 * Time: 下午3:21
 *
 *               ii.                                         ;9ABH,
 *              SA391,                                    .r9GG35&G
 *              &#ii13Gh;                               i3X31i;:,rB1
 *              iMs,:,i5895,                         .5G91:,:;:s1:8A
 *               33::::,,;5G5,                     ,58Si,,:::,sHX;iH1
 *                Sr.,:;rs13BBX35hh11511h5Shhh5S3GAXS:.,,::,,1AG3i,GG
 *                .G51S511sr;;iiiishS8G89Shsrrsh59S;.,,,,,..5A85Si,h8
 *               :SB9s:,............................,,,.,,,SASh53h,1G.
 *            .r18S;..,,,,,,,,,,,,,,,,,,,,,,,,,,,,,....,,.1H315199,rX,
 *          ;S89s,..,,,,,,,,,,,,,,,,,,,,,,,....,,.......,,,;r1ShS8,;Xi
 *        i55s:.........,,,,,,,,,,,,,,,,.,,,......,.....,,....r9&5.:X1
 *       59;.....,.     .,,,,,,,,,,,...        .............,..:1;.:&s
 *      s8,..;53S5S3s.   .,,,,,,,.,..      i15S5h1:.........,,,..,,:99
 *      93.:39s:rSGB@A;  ..,,,,.....    .SG3hhh9G&BGi..,,,,,,,,,,,,.,83
 *      G5.G8  9#@@@@@X. .,,,,,,.....  iA9,.S&B###@@Mr...,,,,,,,,..,.;Xh
 *      Gs.X8 S@@@@@@@B:..,,,,,,,,,,. rA1 ,A@@@@@@@@@H:........,,,,,,.iX:
 *     ;9. ,8A#@@@@@@#5,.,,,,,,,,,... 9A. 8@@@@@@@@@@M;    ....,,,,,,,,S8
 *     X3    iS8XAHH8s.,,,,,,,,,,...,..58hH@@@@@@@@@Hs       ...,,,,,,,:Gs
 *    r8,        ,,,...,,,,,,,,,,.....  ,h8XABMMHX3r.          .,,,,,,,.rX:
 *   :9, .    .:,..,:;;;::,.,,,,,..          .,,.               ..,,,,,,.59
 *  .Si      ,:.CTHBMMMMMB&5,....                    .            .,,,,,.sMr
 *  SS       :: h@@@@@@@@@@#; .                     ...  .         ..,,,,iM5
 *  91  .    ;:.,1&@@@@@@MXs.                            .          .,,:,:&S
 *  hS ....  .:;,,,i3MMS1;..,..... .  .     ...                     ..,:,.99
 *  ,8; ..... .,:,..,8Ms:;,,,...                                     .,::.83
 *   s&: ....  .sS553B@@HX3s;,.    .,;13h.                            .:::&1
 *    SXr  .  ...;s3G99XA&X88Shss11155hi.                             ,;:h&,
 *     iH8:  . ..   ,;iiii;,::,,,,,.                                 .;irHA
 *      ,8X5;   .     .......                                       ,;iihS8Gi
 *         1831,                                                 .,;irrrrrs&@
 *           ;5A8r.                                            .:;iiiiirrss1H
 *             :X@H3s.......                                .,:;iii;iiiiirsrh
 *              r#h:;,...,,.. .,,:;;;;;:::,...              .:;;;;;;iiiirrss1
 *             ,M8 ..,....,.....,,::::::,,...         .     .,;;;iiiiiirss11h
 *             8B;.,,,,,,,.,.....          .           ..   .:;;;;iirrsss111h
 *            i@5,:::,,,,,,,,.... .                   . .:::;;;;;irrrss111111
 *            9Bi,:,,,,......                        ..r91;;;;;iirrsss1ss1111
 */

namespace Quan\System;

use Phalcon\Mvc\Application;
use Phalcon\Cli\Console;
use \Quan\System\Init\Cli\Services as CliServices;
use \Quan\System\Init\Sapi\Services as SapiServices;

class System
{
    /***
     * @param bool $send
     * @return bool|\Phalcon\Http\ResponseInterface
     */
    public static function run($send = true)
    {
        define('ENVIROMENT', getenv('GAORE_ENVIRONMENT') ? : 'production');

        switch (ENVIROMENT) {
            case 'production':
                error_reporting(0);
                break;
            case 'testing':
                error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
                break;
            default:
                // development
                error_reporting(E_ALL & ~E_NOTICE);
        }

        global $argv;

        $sapi = PHP_SAPI === 'cli' ? 'cli' : 'sapi';

        if (PHP_SAPI === 'cli') {
            set_time_limit(0);

            $arguments = [];
            foreach ($argv as $k => $arg) {
                if ($k === 1) {
                    $arguments["module"] = $arg;
                } elseif ($k === 2) {
                    $arguments["task"] = $arg;
                } elseif ($k === 3) {
                    $arguments["action"] = $arg;
                } elseif ($k >= 4) {
                    $arguments['params'][] = $arg;
                }
            }
            require SYSTEM_PATH . '/Init/Cli/Services.php';
            $di = new CliServices($sapi);
            $console = new Console($di);
            $console->registerModules($di->getModules());
            $di->setShared('console', $console);
            $console->handle($arguments);
        } else {
            require SYSTEM_PATH . '/Init/Sapi/Services.php';
            $di = new SapiServices($sapi);
            $application = new Application($di);
            $application->useImplicitView(true);
            $application->registerModules($di->getModules());
            $response = $application->handle();
            if ($send !== false) {
                $response->send();
            } else {
                return $response;
            }
        }
    }
}