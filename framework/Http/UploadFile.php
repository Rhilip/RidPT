<?php

namespace Mix\Http;

use SplFileInfo;

/**
 * UploadFile类
 * @author 刘健 <coder.liu@qq.com>
 */
class UploadFile extends SplFileInfo
{

    // 文件名
    public $name;

    // MIME类型
    public $type;

    // 错误码
    public $error;

    /**
     * 创建实例，通过表单名称
     * @param $name
     * @return $this
     */
    public static function newInstanceByName($name)
    {
        $file = \Mix::app()->request->files($name);
        return is_null($file) ? $file : new self($file);
    }

    // 构造
    public function __construct($file)
    {
        parent::__construct($file['tmp_name']);
        $this->name    = $file['name'];
        $this->type    = $file['type'];
        $this->error   = $file['error'];
    }

    // 文件另存为
    public function saveAs($filename)
    {
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $bytes = file_put_contents($filename, file_get_contents($this->getPathname()));
        return $bytes ? true : false;
    }

    // 获取基础名称
    public function getBaseName($suffix = null)
    {
        return $this->name;
    }

    // 获取扩展名
    public function getExtension()
    {
        return pathinfo($this->name)['extension'];
    }
}
