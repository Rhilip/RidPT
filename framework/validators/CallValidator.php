<?php

namespace mix\validators;

/**
 * CallValidator类
 * @author 刘健 <coder.liu@qq.com>
 */
class CallValidator extends BaseValidator
{

    // 启用的选项
    protected $_enabledOptions = ['callback'];

    // 回调验证
    protected function callback($param)
    {
        $value = $this->attributeValue;
        list($res,$errmsg) = call_user_func_array($param, [$value]);
        if (!$res) {
            // 设置错误消息
            $defaultMessage = $errmsg ?: "{$this->attribute}是无效的值.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

}
