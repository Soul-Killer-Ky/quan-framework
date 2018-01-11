<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/11/23
 * Time: 下午4:40
 */

namespace Quan\System\Mvc\Model;


use Quan\System\Mvc\Model\Query\Modifier;

class Query extends \Phalcon\Mvc\Model\Query
{
    public function parse()
    {
        $irPhql = parent::parse();
        $modifyer = new Modifier($irPhql);
        $irPhql = $modifyer->run(function ($struct) use ($irPhql) {

            $bindTypes = $this->getBindTypes();
            $bindParams = $this->getBindParams();

            foreach ($irPhql['models'] as $offset => $modelname) {

                $modelmanager = $this->getDI()->get('modelsManager');
                $model = $this->_modelsInstances[$modelname] ? : $modelmanager->getInitializedModel($modelname);
                $classuses = class_uses($model);
                $tableid = isset($bindTypes['_tableid']) ? $bindTypes['_tableid'] : null;
                $tablename = $model->getTableName();
                $hasPartitionMethod = method_exists($model, '_tablePartition');

                if (in_array('Quan\System\Mvc\Model\PartitionModel', $classuses)) {
                    $field = $model->getTablePartitionField();
                    $count = $model->getTablePartitionCount();
                } else {
                    $count = 0;
                }

                if (isset($model->_columnMap) && $model->_columnMap) {
                    foreach ($model->_columnMap as $key => $value) {
                        if (isset($struct[$key])) {
                            $tmp = $struct[$key];
                            unset($struct[$key]);
                            $struct[$value] = $tmp;
                        }
                    }
                }

                if (isset($field)) {
                    $field = $tablename. '.'. $field;
                    if (isset($struct[$field]) && $hasPartitionMethod) {
                        $type = $struct[$field][0]['type'];
                        $value = $struct[$field][0]['value'];
                        if ($type == 'placeholder') {
                            $value = substr_replace($value, '', 0, 1);
                            $value = $bindParams[$value];
                        }
                        $tableid = call_user_func_array(array($model, '_tablePartition'), array($value, $count));
                    } elseif (!is_null($tableid) && $hasPartitionMethod) {
                        $tableid = strval($tableid);
                    }
                }
            }

            return [$tablename, $tableid];
        });

        $this->_intermediate = $irPhql;
        return $irPhql;
    }
}