<?php

static $_config;


if (! function_exists('config')) {
    /**
     * 获取配置参数
     *
     * @param  array|string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function config($key, $default = null)
    {
        $conf = new Config();
        // 优先执行设置获取或赋值
        if (is_string($key)) {
            $value = $conf->get($key);
            return $value === null ? $default : $value;
        }
        // 设置配置
        if (is_array($key)){
            $conf->set($key);
        }
        // 避免非法参数
        return null;
    }
}

if (! function_exists('base_path')) {
    function base_path($path = null)
    {
        if ($path == null) {
            return $_SERVER['DOCUMENT_ROOT'];
        }
        return $_SERVER['DOCUMENT_ROOT']. '/'.$path;
    }
}