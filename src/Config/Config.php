<?php

declare (strict_types = 1);

namespace Larke\Admin\Configs\Config;

use Iterator;
use ArrayAccess;

use Illuminate\Support\Facades\DB;
use Larke\Admin\Configs\Model\Config as ConfigModel;

/**
 * 配置类
 */
class Config implements Iterator, ArrayAccess
{
    // 存$key的数组，非$value
    private $array = []; 

    /**
     * @var array 原始db数据数组
     */
    protected $data = [];

    /**
     * @var array 存储Config相应key-value数值的数组
     */
    protected $kvdata = [];

    /**
     * @var array 存储Config相应原始数据的数组
     */
    protected $origkvdata = [];

    /**
     * @param string $itemName
     */
    public function __construct($itemName = '')
    {
        if ($itemName) {
            $itemName = filter_correct_name($itemName);
        }

        $this->data['Name'] = $itemName;
        $this->position = 0;
    }
    
    /**
     * 导入数据
     *
     * @return void
     */
    public function loadInfo($key, $value)
    {
        $value = $this->unserializeData($value);
        $this->kvdata[$key] = $value;
        $this->origkvdata[$key] = $value;
    }

    /**
     * 获取 Item 名
     *
     * @return string
     */
    public function getItemName()
    {
        return $this->data['Name'];
    }

    /**
     * 获取 Data 数据
     *
     * @return array
     */
    public function getData()
    {
        return $this->kvdata;
    }

    /**
     * 检查 kvdata 是否存在 key
     *
     * @param string $name key名
     *
     * @return bool
     */
    public function hasKey($name)
    {
        return array_key_exists($name, $this->kvdata);
    }

    /**
     * @param string $name key名
     *
     * @return mixed
     */
    public function getKey($name)
    {
        if (! isset($this->kvdata[$name])) {
            return null;
        }

        return $this->kvdata[$name];
    }
    
    /**
     * 添加 or 修改 Key
     *
     * @param $name
     *
     * @return bool
     */
    public function addKey($name, $value)
    {
        $name = filter_correct_name($name);
        if (! $name) {
            return false;
        }
        
        $this->kvdata[$name] = $value;
        return true;
    }

    /**
     * 删除 Key
     *
     * @param $name
     *
     * @return bool
     */
    public function delKey($name)
    {
        $name = filter_correct_name($name);
        if (array_key_exists($name, $this->kvdata) == false) {
            return false;
        }

        unset($this->kvdata[$name]);

        return true;
    }

    /**
     * 获取数量
     *
     * @return int
     */
    public function countItem()
    {
        return count($this->kvdata);
    }

    public function countItemOrig()
    {
        return count($this->origkvdata);
    }

    /**
     * 保存数据
     *
     * @return bool
     */
    public function save()
    {
        $name = $this->getItemName();
        if ($name == '') {
            return false;
        }

        $add = array_diff_key($this->kvdata, $this->origkvdata);
        $del = array_diff_key($this->origkvdata, $this->kvdata);
        
        $mod = [];
        foreach ($this->kvdata as $key => $value) {
            if (array_key_exists($key, $this->origkvdata) && 
                $this->kvdata[$key] != $this->origkvdata[$key]) {
                $mod[$key] = $value;
            }
        }

        if (($add + $del + $mod) == []) {
            return true;
        }

        // 如果存在老数据，先删除旧的
        $old3 = ConfigModel::where([
                'name' => $name,
            ])
            ->get()
            ->toArray();
        if (! empty($old3)) {
            $del = [];
            $mod = [];
            $add = $this->kvdata;
            
            ConfigModel::where([
                    'name' => $name,
                ])
                ->delete();
        }
        
        if (($add + $del + $mod) == []) {
            return true;
        }

        $sqls = [];
        $sqls['insert'] = [];
        $sqls['update'] = [];
        $sqls['delete'] = [];

        // add
        foreach ($add as $key2 => $value2) {
            $old4 = ConfigModel::where([
                    'name' => $name,
                    'key'  => $key2,
                ])
                ->first();
            if (empty($old4)) {
                $sqls['insert'][] = function() use($name, $key2, $value2) {
                    ConfigModel::create([
                        'name'  => $name,
                        'key'   => $key2,
                        'value' => $this->serializeData($value2),
                    ]);
                };
            } else {
                $sqls['update'][] = function() use($name, $key2, $value2) {
                    ConfigModel::where([
                            'name' => $name,
                            'key'  => $key2,
                        ])
                        ->update([
                            'value' => $this->serializeData($value2),
                        ]);
                };
            }
        }
        
        // mod
        foreach ($mod as $key3 => $value3) {
            $old5 = ConfigModel::where([
                    'name' => $name,
                    'key'  => $key3,
                ])
                ->first();
            if (empty($old5)) {
                $sqls['insert'][] = function() use($name, $key3, $value3) {
                    $res = ConfigModel::create([
                        'name'  => $name,
                        'key'   => $key3,
                        'value' => $this->serializeData($value3),
                    ]);
                };
            } else {
                $sqls['update'][] = function() use($name, $key3, $value3) {
                    ConfigModel::where([
                            'name' => $name,
                            'key'  => $key3,
                        ])
                        ->update([
                            'value' => $this->serializeData($value3),
                        ]);
                };
            }
        }
        
        // del
        foreach ($del as $key33 => $value33) {
            $sqls['delete'][] = function() use($name, $key33) {
                    ConfigModel::where([
                            'name' => $name,
                            'key'  => $key33,
                        ])
                        ->delete();
                };;
        }
        
        Db::beginTransaction();
        try {
            foreach ($sqls['insert'] as $key => $model) {
                $model();
            }
            
            foreach ($sqls['update'] as $key => $model) {
                $model();
            }
            
            foreach ($sqls['delete'] as $key => $model) {
                $model();
            }

            Db::commit();
        } catch (Exception $e) {
            Db::rollBack();
        }

        // 存储成功后重置origkvdata
        $this->origkvdata = $this->kvdata;

        return true;
    }
    
    /**
     * 删除数据
     *
     * @return bool
     */
    public function delete()
    {
        $name = $this->getItemName();
        
        $res = ConfigModel::where([
                'name' => $name,
            ])
            ->delete();
        if ($res === false) {
            return false;
        }

        return true;
    }

    /**
     * 序列化
     *
     * @return string 返回序列化的值
     */
    public function serialize()
    {
        if (count($this->kvdata) == 0) {
            return '';
        }

        $array = $this->kvdata;
        return serialize($array);
    }

    /**
     * 反序列化
     *
     * @param string $value 序列化值
     *
     * @return bool
     */
    public function unserialize($value)
    {
        if (empty($value)) {
            return false;
        }

        $this->kvdata = @unserialize($value);
        if (! is_array($this->kvdata)) {
            $this->kvdata = [];

            return false;
        }

        return true;
    }

    public function serializeData($value)
    {
        return serialize($value);
    }

    public function unserializeData($value)
    {
        $s = @unserialize($value);

        return $s;
    }

    private $position = 0;

    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->array = array_keys($this->kvdata);
        $this->position = 0;
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->kvdata[$this->array[$this->position]];
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->array[$this->position];
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        ++$this->position;
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        return array_key_exists($this->position, $this->array);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->hasKey($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->getKey($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->addKey($offset, $value);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        $this->delKey($offset);
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->addKey($name, $value);
    }

    /**
     * @param string $name key名
     *
     * @return null
     */
    public function __get($name)
    {
        return $this->getKey($name);
    }

    /**
     * @param $name
     */
    public function __isset($name)
    {
        return $this->hasKey($name);
    }

    /**
     * @param $name
     */
    public function __unset($name)
    {
        $this->delKey($name);
    }

    /**
     * 返回JSON数据
     *
     * @return string
     */
    public function __toString()
    {
        return (string) json_encode($this->kvdata);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->kvdata;
    }

    /**
     * DebugInfo >= php 5.6
     */
    public function __debugInfo()
    {
        $array = [];
        foreach ($this as $key => $value) {
            $array[$key] = $value;
        }
        
        return $array;
    }

}
