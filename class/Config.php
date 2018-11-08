<?php

class  Config
{
    private $config_key = [];

    private $no_config_file = false;

    static $config = [];

    public function get($key){
        if ($this->no_config_file) {
            return null;
        }
        if (!strpos($key, '.')) {
            if (isset(self::$config[$key])) {
                return self::$config[$key];
            }
            return $this->loadConfig($key)->get($key);
        }
        $this->config_key = explode('.', $key);
        $file_key         = $this->config_key[0];
        if (isset(self::$config[$file_key])) {
            return $this->getVal(self::$config);
        }
        return $this->loadConfig($file_key)->get($key);
    }


    public function set($values = [])
    {
        foreach ($values as $key => $value) {
            if (!strpos($key, '.')) {
                continue;
            }
            $config_key = explode('.', $key);
            $this->setVal($config_key, $value);
        }
    }

    /**
     * 加载配置文件 支持格式转换 仅支持一级配置
     *
     * @param $file_key
     *
     * @return $this
     */
    function loadConfig($file_key){
        $config_file = "config/{$file_key}.php";
        self::$config[$file_key] = @include_once($config_file);
        if (self::$config[$file_key] == false) {
            $this->no_config_file = true;
        }
        return $this;
    }

    /**
     * 根据配置key,在配置数组内获取配置
     *
     * @param     $config_data
     * @param int $pos
     *
     * @return null
     */
    private function getVal($config_data, $pos = 0) {
        $key = $this->config_key[$pos];
        if (isset($config_data[$key])) {
            $sub_config_data = $config_data[$key];
            $len             = count($this->config_key);
            if ($pos < $len - 1) {
                $pos++;
                return $this->getVal($sub_config_data, $pos);
            }
            return $sub_config_data;
        }
        return null;
    }

    private function setVal($config_key, $value) {
        // $this->config_key = array_reverse($config_key);
        if (count($config_key) > 1) {
            $last_key       = array_pop($config_key);
            $temp_key_array = implode('.', $config_key);
            $temp_data      = $this->get($temp_key_array);
            if ($temp_data != null) {
                $temp_data[$last_key] = $value;
                $this->setVal($config_key, $temp_data);
            }
        } else {
            self::$config[$config_key[0]] = $value;
        }
    }
}