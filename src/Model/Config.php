<?php

declare (strict_types = 1);

namespace Larke\Admin\Configs\Model;

use Larke\Admin\Model\Base;

/**
 * 配置
 *
 * @create 2024-7-23
 * @author deatil
 */
class Config extends Base
{
    // 设置当前模型对应的数据表名称
    protected $table = 'configs';
    protected $keyType = 'string';
    
    // 设置主键名
    protected $primaryKey = 'id';
    
    protected $guarded = [];
    
    public $incrementing = false;
    public $timestamps = false;

}
