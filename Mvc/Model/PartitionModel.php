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
     * 重写查询建立链接的时候，计算分表。selectReadConnection 为 Phalcon//Model// 方法
     * @param $intermediate
     * @param $bindParams
     * @param $bindTypes
     * @return mixed
     */
    public function selectReadConnection(&$intermediate, $bindParams, $bindTypes)
    {
        // where 条件找出分表条件
        $conn = parent::getReadConnection();

        $modifier = new QueryModifier($intermediate);
        $prefix = static::$_tableprefix ? : $conn->getDescriptor()['prefix'];
        $field = $this->_tablePartition['field'];
        $count = $this->_tablePartition['count'];
        $intermediate = $modifier->run(

            function ($struct) use ($prefix, $bindParams, $bindTypes, $field, $count) {
                $tablename = $this->getTableName();
                $tableid = $bindTypes['_tableid'];

                if (isset($this->_columnMap) && $this->_columnMap) {
                    foreach ($this->_columnMap as $key => $value) {
                        if (isset($struct[$key])) {
                            $tmp = $struct[$key];
                            unset($struct[$key]);
                            $struct[$value] = $tmp;
                        }
                    }
                }

                if (count($struct[$field]) == 1  && method_exists($this, '_tablePartition')) {
                    $type = $struct[$field][0]['type'];
                    $value = $struct[$field][0]['value'];
                    if ($type == 'placeholder') {
                        $value = substr_replace($value, '', 0, 1);
                        $value = $bindParams[$value];
                    }
                    return call_user_func_array(array($this, '_tablePartition'), array($value, $count));
                } elseif (!is_null($tableid) && method_exists($this, '_tablePartition')) {
                    $field = $this->_tablePartition['field'];
                    $this->$field = $this->$field ? : $tableid;
                    return call_user_func_array(array($this, '_tablePartition'), array($tableid, $count));
                } else {
                    return $prefix. $tablename. (!is_null($this->_tableid) ? '_'. $this->_tableid : '');
                }
            }
        );
        return $conn;
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