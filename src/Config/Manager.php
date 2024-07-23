<?php

declare (strict_types = 1);

namespace Larke\Admin\Configs\Config;

use Larke\Admin\Configs\Model\Config as ConfigModel;

/**
 * 管理器
 */
class Manager
{
    /**
     * @var Config[] 配置选项
     */
    public $configs = [];
    
    /**
     * 导入 Configs 表数据
     */
    public function loadConfigs()
    {
        $configs = ConfigModel::orderBy('name', 'ASC')->get()->toArray();
        
        foreach ($configs as $cfg) {
            $l = $this->config($cfg['name']);
            if ($l) {
                $l->loadInfo($cfg['key'], $cfg['value']);
            }
        }
    }

    /**
     * 保存 Configs 表
     *
     * @param string $name Configs表名
     *
     * @return bool
     */
    public function saveConfig($name)
    {
        if (! isset($this->configs[$name])) {
            return false;
        }

        $this->configs[$name]->save();

        return true;
    }

    /**
     * 删除 Configs 表
     *
     * @param string $name Configs表名
     *
     * @return bool
     */
    public function delConfig($name)
    {
        if (! isset($this->configs[$name])) {
            return false;
        }

        $this->configs[$name]->delete();
        unset($this->configs[$name]);

        return true;
    }

    /**
     * 获取 Configs 表值
     *
     * @param string $name Configs表名
     *
     * @return mixed
     */
    public function config($name)
    {
        if (! isset($this->configs[$name])) {
            $name = filter_correct_name($name);
            if (! $name) {
                return null;
            }

            $this->configs[$name] = new Config($name);
        }

        return $this->configs[$name];
    }

    /**
     * Config 是否存在.
     *
     * @param string $name Configs表名
     *
     * @return bool
     */
    public function hasConfig($name)
    {
        return isset($this->configs[$name]) && $this->configs[$name]->countItem() > 0;
    }
}
