<?php

namespace mix\validators;

/**
 * DoubleValidator类
 * @author 刘健 <coder.liu@qq.com>
 */
class DoubleValidator extends BaseValidator
{

    // 初始化选项
    protected $_initOptions = ['double'];

    // 启用的选项
    protected $_enabledOptions = ['unsigned', 'min', 'max', 'length', 'minLength', 'maxLength'];

    // 类型验证
    protected function double()
    {
        $value = $this->attributeValue;
        if (!Validate::isDouble($value)) {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}只能为小数.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

}
