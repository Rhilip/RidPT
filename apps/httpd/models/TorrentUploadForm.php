<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/21
 * Time: 21:45
 */

namespace apps\httpd\models;


use Mix\Facades\Config;
use Mix\Validators\Validator;

class TorrentUploadForm extends Validator
{
    public $name;

    /**  @var \mix\Http\UploadFile */
    public $file;

    public $descr;

    public $uplver = "no";

    // 规则
    public function rules()
    {
        return [
            'name' => ['string', 'filter' => ['trim', 'strip_tags', 'htmlspecialchars']],
            'file' => ['file', 'mimes' => ["application/x-bittorrent"], 'maxSize' => Config::get("torrent.max_file_size")],
            'descr' => ['string', 'filter' => ['trim', 'strip_tags', 'htmlspecialchars']],
            'uplver' => ['in', 'range' => ['yes', 'no'], 'strict' => true],
        ];
    }

    // 场景
    public function scenarios()
    {
        return [
            'upload' => [
                'required' => ['name', 'file', 'descr'],
                'optional' => ['uplver']
            ],
        ];
    }

    // 消息
    public function messages()
    {
        return [
            'file.mimes' => '文件类型不支持.',
            'file.maxSize' => '文件大小不能超过1MB.',
        ];
    }
}
