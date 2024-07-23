<?php

declare (strict_types = 1);

namespace Larke\Admin\Configs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

use Larke\Admin\Extension\ServiceProvider as BaseServiceProvider;

use Larke\Admin\Configs\Config\Manager;

// 文件夹
use Larke\Admin\Configs\Model;
use Larke\Admin\Configs\Observer;

use function Larke\Admin\register_install_hook;
use function Larke\Admin\register_uninstall_hook;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * 包名
     */
    protected $pkg = "larke/configs";
    
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->registerBind();
    }
    
    /**
     * 初始化
     */
    public function boot()
    {
        // 扩展注册
        $this->addExtension(
            name: __CLASS__, 
        );
    }
    
    /**
     * 在扩展安装、扩展卸载等操作时有效
     */
    public function action()
    {
        register_install_hook($this->pkg, [$this, 'install']);
        register_uninstall_hook($this->pkg, [$this, 'uninstall']);
    }

    /**
     * 运行中
     */
    public function start()
    {
        // 模型事件
        $this->bootObserver();
        
        // 启用时自动导入数据
        app('larke-admin.configs')->loadConfigs();
    }
    
    /**
     * 绑定
     *
     * @return void
     */
    protected function registerBind()
    {
        // 配置
        $this->app->singleton('larke-admin.configs', Manager::class);
    }

    /**
     * 模型事件
     *
     * @return void
     */
    protected function bootObserver()
    {
        Model\Config::observe(new Observer\Config());
    }

    /**
     * 安装后
     */
    protected function install()
    {
        // 执行数据库
        $sqlFile = __DIR__.'/../resources/database/install.sql';
        $this->runSql($sqlFile);
    }
    
    /**
     * 卸载后
     */
    protected function uninstall()
    {
        // 执行数据库
        $sqlFile = __DIR__.'/../resources/database/uninstall.sql';
        $this->runSql($sqlFile);
    }

    /**
     * 执行 sql
     */
    protected function runSql($file)
    {
        $sqlData = File::get($file);
        if (! empty($sqlData)) {
            $dbPrefix = DB::getConfig('prefix');
            $sqlContent = str_replace('pre__', $dbPrefix, $sqlData);
            
            DB::unprepared($sqlContent);
        }
    }
}
