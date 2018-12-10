<?php

namespace mix\validators;

/**
 * InValidator类
 * @author 刘健 <coder.liu@qq.com>
 */
class InValidator extends BaseValidator
{

    // 启用的选项
    protected $_enabledOptions = ['range', 'strict'];

    // 范围验证
    protected function range($param)
    {
        $value  = $this->attributeValue;
        $strict = empty($this->_settings['strict']) ? false : true;
        if (!Validate::in($value, $param, $strict)) {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}不在" . implode(',', $param) . "范围内.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

}
