<?php

namespace mix\validators;

/**
 * AlphaValidator类
 * @author 刘健 <coder.liu@qq.com>
 */
class AlphaValidator extends BaseValidator
{

    // 初始化选项
    protected $_initOptions = ['alpha'];

    // 启用的选项
    protected $_enabledOptions = ['length', 'minLength', 'maxLength'];

    // 类型验证
    protected function alpha()
    {
        $value = $this->attributeValue;
        if (!Validate::isAlpha($value)) {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}只能为字母.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

}
