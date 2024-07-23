<?php

declare (strict_types = 1);

namespace Larke\Admin\Configs\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * 配置
 *
 * @create 2024-7-23
 * @author deatil
 */
class Config extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'larke-admin.configs';
    }
}
