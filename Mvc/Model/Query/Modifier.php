<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2016/11/29
 * Time: 上午10:34
 * 分表查询修改器
 */
namespace Quan\System\Mvc\Model\Query;

class Modifier
{
    private $tablename = '';

    private $intermediate;

    public function __construct($intermediate = [])
    {
        $this->intermediate = $intermediate;
    }


    /**
     * 注入修改表名的方法
     * @param callable|null $func
     * @return array
     */
    public function run(callable $func = null)
    {
        $result = $this->loopWhere($this->intermediate['where']);
        $values = [];
        foreach ($result as $res) {
            if (isset($res['field'])) {
                $last = $res['field']['domain']. '.'. $res['field']['name'];
            } elseif (isset($last)) {
                $values[$last][] = $res['value'];
            }
        }

        if (is_callable($func)) {
            list($tablename, $tableid)  = call_user_func_array($func, array($values));
            if (!is_null($tableid)) {
                $this->tablename = $tablename. "_" . $tableid;
            } else {
                $this->tablename = $tablename;
            }
        }

        $this->intermediate = $this->modifyColumns($this->intermediate);
        $this->intermediate = $this->modifyOrder($this->intermediate);
        $this->intermediate = $this->modifyGroup($this->intermediate);
        $this->loopWhere($this->intermediate['where'], $this->tablename);

        $num = array_search($this->tablename, $this->intermediate['tables']);
        if ($num !== false){
            $pos = strrpos($this->tablename, '_') ;
            $endString = substr($this->tablename, $pos + 1);
            if (is_numeric($endString)) {
                $this->intermediate['models'][$num] .= "\\{$endString}";
            }
        }

        $this->intermediate = $this->modifyGroup($this->intermediate);
        $this->loopWhere($this->intermediate['where'], $this->tablename);
        return $this->intermediate;
    }

    /**
     * Modify column option of intermediate data with the real table name
     * @param $intermediate
     * @return mixed
     */
    public function modifyColumns($intermediate)
    {
        $intermediate['tables'][0] = $this->tablename;
        foreach ($intermediate['columns'] as $key => $column) {

            if (is_array($column['column'])) {

                if (isset($column['column']['domain'])) {
                    $column['column']['domain'] = $this->tablename;
                }

                if (isset($column['column']['arguments'])) {
                    foreach ($column['column']['arguments'] as $k => $argument) {
                        if ($argument['type'] != 'all') {
                            $argument['domain'] = $this->tablename;
                        }
                        $column['column']['arguments'][$k] = $argument;
                    }
                }
            } else {
                $column['column'] = $this->tablename;
            }

            $intermediate['columns'][$key] = $column;
        }
        return $intermediate;
    }

    /**
     * Modify order option of intermediate data with the real table name
     * @param $intermediate
     * @return mixed
     */
    public function modifyOrder($intermediate)
    {
        if (isset($intermediate['order'])) {
            foreach ($intermediate['order'] as $key => $orders) {
                if ($orders[0]['domain']) {
                    $orders[0]['domain'] = $this->tablename;
                }
                $intermediate['order'][$key] = $orders;
            }
        }
        return $intermediate;
    }

    /**
     * Modify order option of intermediate data with the real table name
     * @param $intermediate
     */
    public function modifyGroup(&$intermediate)
    {
        if (isset($intermediate['group'])) {
            foreach ($intermediate['group'] as $key => $group) {
                if ($group['domain']) {
                    $group['domain'] = $this->tablename;
                }
                $intermediate['group'][$key] = $group;
            }
        }
        return $intermediate;
    }

    /**
     * @param $condition
     * @param string $domain
     * @return array
     */
    public function loopWhere(&$condition, $domain = '')
    {
        $a = [];

        if (isset($condition['left']) && $condition['left']) {
            $a = array_merge($a, $this->loopWhere($condition['left'], $domain));
        }
        if (isset($condition['right']) && $condition['right']) {
            $a = array_merge($a, $this->loopWhere($condition['right'], $domain));
        }

        if (!isset($condition['left']) && !isset($condition['right'])) {
            // 这里进行修改
            if (isset($condition['domain']) && $condition['domain']) {
                $a[]['field'] = $condition;
                $condition['domain'] = $domain ? : $condition['domain'];
            } else {
                $a[]['value'] = $condition;
            }
        }

        return $a;
    }

    /**
     *
     * @param string $table
     */
    public function setSource($table = '')
    {
        $this->tablename = $table;
    }
    
}