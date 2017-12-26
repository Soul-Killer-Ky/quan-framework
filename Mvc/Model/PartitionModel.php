<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2016/11/30
 * Time: 上午11:35
 */
namespace Quan\System\Mvc\Model;


trait PartitionModel
{
    public static function find($parameters = [])
    {
        $parameters['partition'] = true;
        return parent::find($parameters);
    }

    public static function count($parameters = [])
    {
        $parameters['partition'] = true;
        return parent::count($parameters);
    }

    public static function sum($parameters = [])
    {
        $parameters['partition'] = true;
        return parent::sum($parameters);
    }

    public static function findFirst($parameters = [])
    {
        $parameters['partition'] = true;
        return parent::findFirst($parameters);
    }

    public static function maximum($parameters = [])
    {
        $parameters['partition'] = true;
        return parent::maximum($parameters);
    }

    public static function minimum($parameters = [])
    {
        $parameters['partition'] = true;
        return parent::minimum($parameters);
    }

    public static function average($parameters = [])
    {
        $parameters['partition'] = true;
        return parent::average($parameters);
    }

    /**
     * @param null $parameters
     * @param string $tableid
     * @return mixed
     */
    public static function findFromTab($parameters = null, $tableid = '')
    {
        if ($tableid) {
            $parameters['bindTypes']['_tableid'] = $tableid;
        }
        return  self::find($parameters);
    }

    /***
     * @return mixed
     */
    public function getTablePartitionField()
    {
        return $this->_tablePartition['field'];
    }

    /***
     * @return mixed
     */
    public function getTablePartitionCount()
    {
        return $this->_tablePartition['count'];
    }

    /**
     * 重写删除前分表
     * @return mixed
     */
    public function beforeDelete()
    {
        $field = $this->_tablePartition['field'];
        $count = $this->_tablePartition['count'];
        $tablename =  call_user_func_array([$this, '_tablePartition'], [$this->$field, $count]);
        $this->setSource($tablename);
        return $tablename;
    }

    /**
     * 重写更新前分表
     * @return mixed
     */
    public function prepareSave()
    {
        $field = $this->_tablePartition['field'];
        $count = $this->_tablePartition['count'];
        $tablename =  call_user_func_array([$this, '_tablePartition'], [$this->$field, $count]);
        $this->setSource($tablename);
        return $tablename;
    }
}