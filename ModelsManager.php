<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2016/11/28
 * Time: 下午6:50
 *
 * 自定义modelManager
 * ----------------------+
 * 不要随便修改核心类!!!    |
 * --------------------- |                  ,s555SB@@&
 *                      \/                :9H####@@@@@Xi
 *                                     1@@@@@@@@@@@@@@8
 *                                   ,8@@@@@@@@@B@@@@@@8
 *                                  :B@@@@X3hi8Bs;B@@@@@Ah,
 *             ,8i                  r@@@B:     1S ,M@@@@@@#8;
 *            1AB35.i:               X@@8 .   SGhr ,A@@@@@@@@S
 *            1@h31MX8                18Hhh3i .i3r ,A@@@@@@@@@5
 *            ;@&i,58r5                 rGSS:     :B@@@@@@@@@@A
 *             1#i  . 9i                 hX.  .: .5@@@@@@@@@@@1
 *              sG1,  ,G53s.              9#Xi;hS5 3B@@@@@@@B1
 *               .h8h.,A@@@MXSs,           #@H1:    3ssSSX@1
 *               s ,@@@@@@@@@@@@Xhi,       r#@@X1s9M8    .GA981
 *               ,. rS8H#@@@@@@@@@@#HG51;.  .h31i;9@r    .8@@@@BS;i;
 *                .19AXXXAB@@@@@@@@@@@@@@#MHXG893hrX#XGGXM@@@@@@@@@@MS
 *                s@@MM@@@hsX#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@&,
 *              :GB@#3G@@Brs ,1GM@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@B,
 *            .hM@@@#@@#MX 51  r;iSGAM@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@8
 *          :3B@@@@@@@@@@@&9@h :Gs   .;sSXH@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@:
 *      s&HA#@@@@@@@@@@@@@@M89A;.8S.       ,r3@@@@@@@@@@@@@@@@@@@@@@@@@@@r
 *   ,13B@@@@@@@@@@@@@@@@@@@5 5B3 ;.         ;@@@@@@@@@@@@@@@@@@@@@@@@@@@i
 *  5#@@#&@@@@@@@@@@@@@@@@@@9  .39:          ;@@@@@@@@@@@@@@@@@@@@@@@@@@@;
 *  9@@@X:MM@@@@@@@@@@@@@@@#;    ;31.         H@@@@@@@@@@@@@@@@@@@@@@@@@@:
 *   SH#@B9.rM@@@@@@@@@@@@@B       :.         3@@@@@@@@@@@@@@@@@@@@@@@@@@5
 *     ,:.   9@@@@@@@@@@@#HB5                 .M@@@@@@@@@@@@@@@@@@@@@@@@@B
 *           ,ssirhSM@&1;i19911i,.             s@@@@@@@@@@@@@@@@@@@@@@@@@@S
 *              ,,,rHAri1h1rh&@#353Sh:          8@@@@@@@@@@@@@@@@@@@@@@@@@#:
 *            .A3hH@#5S553&@@#h   i:i9S          #@@@@@@@@@@@@@@@@@@@@@@@@@A.
 */
namespace Quan\System;

use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\Query\Builder;
use Quan\System\Mvc\Model;

class ModelsManager extends Manager
{
    protected $_initialized;

    public $_curfindParam = null;

    public function createBuilder($params = null)
    {
        $dependencyInjector = $this->_dependencyInjector;

        if (!is_object($dependencyInjector)) {
            throw new \Exception("A dependency injection object is required to access ORM services");
        }

        if ($params['partition'] == true) {
            return $dependencyInjector->get("Quan\\System\\Mvc\\Model\\Query\\Builder",
                [
                    $params,
                    $dependencyInjector
                ]
            );
        } else {
            return $dependencyInjector->get('Phalcon\\Mvc\\Model\\Query\\Builder',
                [
                    $params,
                    $dependencyInjector
                ]
            );
        }
    }

    public function getModelTableName($model)
    {
        $modelname = get_class($model);
        return $this->getInitializedModel($modelname)->_tablename;
    }

    public function getInitializedModel($modelname)
    {
        $model = $this->_initialized[strtolower($modelname)];
        return $model;
    }

    public function load($modelName, $newInstance = false)
    {
        $pos = strrpos($modelName, '\\') ;
        $endString = substr($modelName, $pos + 1);
        $startString = substr($modelName, 0, $pos);

        if (!is_numeric($endString)) {
            return parent::load($modelName, $newInstance);
        } else {
            $model = $this->_initialized[strtolower($modelName)] ?? false;
            if (!$model) {
                $model = new $startString(null, $this->_dependencyInjector, $this);
                $this->_initialized[strtolower($modelName)] = $model;
            }
            $model->reset();
            return $model;
        }
    }
}