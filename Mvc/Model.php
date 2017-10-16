<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/2/6
 * Time: 下午2:34
 */
namespace Quan\System\Mvc;

class Model extends \Phalcon\Mvc\Model
{
    // 表前缀
    protected static $_tableprefix = null;

    // 分表ID
    protected $_tableid = null;

    // 表的别名
    protected $_tablename = null;

    // 字段映射
    protected $_columnMap = null;

    // 分表规则
    protected $_tablePartition = null;


    // 初始化, (不等于构造)
    public function initialize()
    {
        //$this->addBehavior(new Blameable());                                                  # 记录当前行为
        $conn = $this->getWriteConnection();
        static::$_tableprefix = $conn->getDescriptor()['prefix'];
        $this->_tablename = !is_null($this->_tablename) ? $this->_tablename : parent::getSource();
        $tableid = !is_null($this->_tableid) ? '_'. $this->_tableid : '';
        $tablename = $conn->getDescriptor()['prefix']. $this->_tablename . $tableid;
        $this->setSource($tablename);
        $this->useDynamicUpdate(true);
    }

    // 字段映射
    public function columnMap()
    {
        // 由于 source 字段 和框架保留字冲突，所以这里转换一下
        $metadata = $this->getModelsMetaData();
        $columnMap = $metadata->getAttributes($this);
        $columnMap = array_combine($columnMap, $columnMap);
        if (isset($this->_columnMap) && is_array($this->_columnMap)) {
            $columnMap = array_merge($columnMap, $this->_columnMap);
        }
        return $columnMap;
    }


    public function getTableName()
    {
        if (is_null($this->_tablename)) {
            $this->_tablename = $this->getModelsManager()->getModelTableName($this);
        }
        return $this->_tablename;
    }


    /**
     * 设置当前查询的分表,
     * @param int $num
     * @return string
     */
    public function setPartitionTable($num = 0)
    {
        $this->_tableid = $num;
        return static::$_tableprefix. $this->getTableName(). '_'. $this->_tableid;
    }

    /**
     * 返回消息
     * @param bool $success
     * @param string $message
     * @param int $code
     * @param null $data
     * @return
     */
    protected static function message($success = true, $message = '', $code = 0, $data = null)
    {
        //return new DataEnvelope($success, $message, 0, $data);
    }

    /**
     * 合并参数
     * @param $param1
     * @param $param2
     * @return mixed
     */
    protected static function mergeParams($param1, $param2)
    {
        $param1['conditions'] .=  ' '. $param2['conditions'];
        $param1['bind'] = array_merge((array)$param1['bind'], (array)$param2['bind']);

        if ('*' === trim($param2['columns'])) {
            $param1['columns'] = '*';
        } elseif (isset($param1['columns']) && isset($param2['columns'])) {
            if ($param1['columns'] && $param2['columns']) {
                $param1['columns'] .= ','. $param2['columns'];
                $param1['columns'] = explode(',',  $param1['columns']);
                $param1['columns'] = implode(',', array_unique(array_filter(array_map('trim',  $param1['columns']))));
            }
        }

        if (isset($param2['order'])) {
            $param1['order'] = $param2['order'];
        }
        return $param1;
    }


    public function getParttionFieldValue()
    {
        $field = $this->_tablePartition['field'];
        return $this->$field;
    }

    public function getMessagesString()
    {
        return array_reduce($this->getMessages(), function ($carry, $item) { $carry .= $item->getMessage(); return $carry;}, '');
    }
}