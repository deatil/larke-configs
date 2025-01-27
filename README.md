## 系统通用配置


### 项目介绍

*  本扩展为 `larke-admin` 扩展
*  特性为自定义配置信息


### 环境要求

 - PHP >= 8.0
 - Laravel >= 11.0.0
 - larke-admin >= 2.0.1


### 安装步骤

1、下载安装扩展

```php
composer require larke/configs
```

或者在`本地扩展->扩展管理->上传扩展` 本地上传

2、然后在 `本地扩展->扩展管理->安装/更新` 安装本扩展


### 示例

~~~php
// 保存或者修改数据
$cfg = larke_configs("cms");
$cfg->version = 0.21;
$cfg->data1 = "data11";
$cfg->value = "value111";
$cfg->value2 = "value211";
// 删除数据并保存
// unset($cfg->data); 
$cfg->save();

// 删除全部配置
larke_configs("cms")->delete();

// 获取数据
$value2 = larke_configs("cms")->value2;
~~~ 


### 开源协议

*  本扩展 遵循 `Apache2` 开源协议发布，在保留本扩展版权的情况下提供个人及商业免费使用。 


### 版权

*  该系统所属版权归 deatil(https://github.com/deatil) 所有。
