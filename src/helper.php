<?php

if (! function_exists('filter_correct_name')) {
    /**
     * 获得一个只含数字字母和-线的字符
     */
    function filter_correct_name($value) {
        return preg_replace('|[^0-9a-zA-Z_/-]|', '', $value);
    }
}

if (! function_exists('larke_configs')) {
    /**
     * 配置
     *
     * @param string $name
     * @return mix
     */
    function larke_configs(string $name) {
        return app('larke-admin.configs')->config($name);
    }
}
