<?php

namespace Rid\Http;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * UploadFile类
 */
class UploadFile extends UploadedFile
{

    // 文件名
    public $name;

    // MIME类型
    public $type;

    // 临时文件名
    public $tmpName;

    // 文件尺寸
    public $size;

    /**
     * 创建实例，通过表单名称
     * @param $name
     * @return $this
     */
    public static function newInstanceByName($name)
    {
        $file = \Rid::app()->request->raw_files[$name];
        return is_null($file) ? $file : new self($file);
    }

    // 构造
    public function __construct($file)
    {
        $this->name    = $file['name'];
        $this->type    = $file['type'];
        $this->tmpName = $file['tmp_name'];
        $this->size    = $file['size'];
        parent::__construct($file['tmp_name'], $file['name'], $file['type']?: 'application/octet-stream', $file['error'] ?: UPLOAD_ERR_OK, false);
    }

    // 文件另存为
    public function saveAs($filename)
    {
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $bytes = file_put_contents($filename, file_get_contents($this->tmpName));
        return $bytes ? true : false;
    }

    public function getClientOriginalFileName()
    {
        return pathinfo($this->getClientOriginalName(), PATHINFO_FILENAME);
    }

    // 获取文件内容
    public function getFileContent()
    {
        return file_get_contents($this->getClientOriginalName());
    }
}
