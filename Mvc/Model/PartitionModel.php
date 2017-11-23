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