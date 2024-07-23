<?php

declare (strict_types = 1);

namespace Larke\Admin\Configs\Observer;

use Larke\Admin\Configs\Model\Config as ConfigModel;

class Config
{
    /**
     * 插入到数据库前
     */
    public function creating(ConfigModel $model)
    {
        $model->id = md5(mt_rand(10000, 99999) . microtime());
    }
}
