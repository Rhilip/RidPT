<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/22
 * Time: 10:34
 */

namespace Mix\Validators;


class TorrentValidator extends FileValidator
{
    // 验证器名称
    protected $_name = 'Torrent';

    protected function torrent()
    {
        /**  @var \mix\Http\UploadFile $value */
        $value = $this->attributeValue;
        if ($value->getExtension() !== "torrent") {
            $defaultMessage = '上传扩展错误.';
            $this->setError(__FUNCTION__, $defaultMessage);
            return false;
        }

        return true;
    }
}
