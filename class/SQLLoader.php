<?php
class SQLLoader
{
    private $template_dir;
    private $temp_dir;


    public function __construct()
    {
        $this->template_dir = base_path('template');
        $this->temp_dir = base_path('temp');
        $files = $this->getFiles($this->template_dir);
        if (count($files) > 0) {
            $this->load($files);
        }
    }

    /**
     * 读取模板文件生成sqlx函数
     * @param $files
     */
    public function load($files)
    {
        foreach ($files as $f) {
            $md5      = md5_file($f);
            $pos      = strrpos($f, '/');
            $basename = substr($f, $pos);
            $basename = str_replace('.txt', '', $basename);
            $filename = $basename . $md5 . '.php';
            $file     = $this->temp_dir . $filename;
            // 新模板或者改动过模板
            if (!file_exists($file)) {
                $this->unlinkExpiredFiles($basename);
                $subject = file_get_contents($f);
                $subject = '<?php
' . $subject;
                $subject = $this->replaceIfContent($subject);
                $subject = $this->replaceDefine($subject);
                //$subject = $this->replaceVar($subject);
                file_put_contents($file, $subject);
            }
            require_once $file;
        }
    }

    private function replaceDefine($subject)
    {
        $pattern = "/{{\s*define .*}}[\s\S]*{{\s*end\s*}}/U";
        return preg_replace_callback($pattern, function ($match) {
            foreach ($match as $v) {
                $name = $this->camelCaseName($v);
                $v    = preg_replace("/{{\s*define .*}}/U", '', $v);
                $v    = $this->replaceDefineEnd($v);
                $v    = $this->replaceContent($v);
                $v    = $this->replaceIfContent($v);

                return 'function ' . $name . ' ($params=[]) {
    $sql ="' . $v;
            }
            return '';
        }, $subject);
    }

    /**
     * 替换{{if ***}}***{{end}}的内容
     *
     * @param $subject
     *
     * @return string
     */
    private function replaceIfContent($subject)
    {
        $pattern = "/{{\s*if .*}}[\s\S]*{{\s*end\s*}}/U";
        return preg_replace_callback($pattern, function ($match) {
            foreach ($match as $v) {
                $v = $this->replaceEnd($v);
                return $this->replaceIf($v);
            }
            return '';
        }, $subject);
    }

    private function replaceEnd($subject)
    {
        $pattern = "/{{\s*end\s*}}/";
        return preg_replace_callback($pattern, function () {
            return '";
    }';
        }, $subject);
    }

    private function replaceDefineEnd($subject)
    {
        $pattern = "/{{\s*end\s*}}/";
        return preg_replace_callback($pattern, function () {
            return '    "; 
    return $sql;
}';
        }, $subject);
    }

    /**
     * @param $subject
     *
     * @return null|string|string[]
     */
    private function replaceVar($subject)
    {
        $pattern = "/\.[a-zA-Z0-9_-]+/";
        return preg_replace_callback($pattern, function ($match) {
            foreach ($match as $v) {
                $v = substr($v, 1);
                return '$params[\':' . $v . '\']';
            }
            return '';
        }, $subject);
    }

    private function getVar($subject)
    {
        $pattern = "/\.[a-zA-Z0-9_-]+/";
        $matches = [''];
        preg_match($pattern, $subject, $matches);
        $v   = substr($matches[0], 1);
        $var = '$params[\':' . $v . '\']';
        return $var;
    }

    /**
     * 替换{{if ***}}里面的内容
     * @param $subject
     *
     * @return null|string|string[]
     */
    private function replaceIf($subject)
    {
        $pattern = "/{{\s*if .*}}/";
        return preg_replace_callback($pattern, function ($match) {
            $str    = trim($match[0], "{{}}");
            $str    = str_replace('if', '', $str);
            $str    = trim($str);
            $var    = $this->getVar($str);
            $result = '";
    if (isset(' . $var . ') && ' . $str . ') {
        $sql.="';
            return $this->replaceVar($result);
        }, $subject);
    }

    /**
     * @param $subject
     *
     * @return null|string|string[]
     */
    private function replaceContent($subject)
    {
        $pattern = "/}[\s\S]*\";/U";
        return preg_replace_callback($pattern, function ($match) {
            foreach ($match as $v) {
                // echo $v."<br>";
                if (preg_match("/}[\s]*\";/U", $v)) {
                    return '}';
                }
                if (strstr('tt' . $v, '$sql.=') != null) {
                    return $v;
                }
                $v = trim($v, "}");
                $v = trim($v);
                return '}
    $sql.="
        ' . $v;
            }
            return '';
        }, $subject);
    }

    /**
     * 驼峰式格式化临时文件的方法名
     * @param $subject
     *
     * @return string
     */
    protected function camelCaseName($subject)
    {
        preg_match('/"(.*)"/i', $subject, $m);
        $name = $m[0];
        $name = str_replace('.', ' ', $name);
        $name = trim($name, '"');
        $name = 'ginV ' . $name;
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);
        $name = str_replace('.', '$', $name);
        return lcfirst($name);
    }

    /**
     * 获取当前所有的sql目标文件
     * @param        $dir
     * @param string $suffix
     *
     * @return array
     */
    public function getFiles($dir, $suffix = '.txt')
    {
        $files = [];
        if (@$handle = opendir($dir)) {
            while (($file = readdir($handle)) !== false) {
                if ($file != ".." && $file != ".") { //排除根目录
                    if (is_dir($dir . "/" . $file)) { //如果是子文件夹，就进行递归
                        $files[$file] = $this->getFiles($dir . "/" . $file);
                    } else { //不然就将文件的名字存入数组
                        $pos = strrpos($file, $suffix);
                        if ($pos && $pos == strlen($file) - 4) {
                            $files[] = $dir . "/" . $file;
                        }
                    }
                }
            }
            closedir($handle);
        }
        return $files;
    }

    /**
     * 删除失效的临时sql文件
     * @param $base
     */
    public function unlinkExpiredFiles($base)
    {
        $files = $this->getFiles($this->temp_dir, '.php');
        foreach ($files as $f) {
            $pattern = '/\\' . $base . '.{32}\.php$/';
            $count   = preg_match($pattern, $f);
            if ($count > 0) {
                unlink($f);
            }
        }
    }

}