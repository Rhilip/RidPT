<?php

namespace mix\validators;

use mix\base\BaseObject;

/**
 * 基础验证器类
 * @author 刘健 <coder.liu@qq.com>
 */
class BaseValidator extends BaseObject
{

    // 必填字段
    public $isRequired;

    // 需要验证的选项
    public $options;

    // 当前属性名
    public $attribute;

    // 当前属性值
    public $attributeValue;

    // 全部消息
    public $messages;

    // 全部属性
    public $attributes;

    // 主验证器的引用
    public $mainValidator;

    // 错误
    public $errors = [];

    // 设置
    protected $_settings = [];

    // 初始化选项
    protected $_initOptions = [];

    // 启用的选项
    protected $_enabledOptions = [];

    // 验证
    public function validate()
    {
        // 清扫数据
        $this->errors    = [];
        $this->_settings = [];
        // 验证
        if ($this->required() && $this->scalar() && !is_null($this->attributeValue)) {
            // 预处理
            foreach ($this->options as $name => $option) {
                if (!in_array($name, $this->_enabledOptions)) {
                    throw new \mix\exceptions\ValidatorException("属性 {$this->attribute} 的验证选项 {$name} 不存在");
                }
                // 不存在的选项转为设置
                if (!method_exists($this, $name)) {
                    $this->_settings[$name] = $option;
                    unset($this->options[$name]);
                }
            }
            // 执行初始化选项验证
            foreach ($this->_initOptions as $option) {
                $this->options = array_merge([$option => null], $this->options);
            }
            // 执行全部选项验证
            foreach ($this->options as $name => $param) {
                $success = $this->$name($param);
                if (!$success) {
                    break;
                }
            }
        }
        $result = empty($this->errors);
        // 属性赋值
        $attribute = $this->attribute;
        if (!$result) {
            $this->mainValidator->$attribute = null;
        } else {
            if ($this instanceof \mix\validators\FileValidator) {
                // 实例化文件对象
                $this->mainValidator->$attribute = \mix\http\UploadFile::newInstanceByName($attribute);
            } else {
                // 属性赋值
                $this->mainValidator->$attribute = $this->attributeValue;
            }
        }
        // 返回
        return $result;
    }

    // 获取消息
    protected function getMessage($attribute, $option)
    {
        $messages = $this->messages;
        if (isset($messages["{$attribute}.{$option}"])) {
            return $messages["{$attribute}.{$option}"];
        }
        if (isset($messages[$attribute])) {
            return $messages[$attribute];
        }
        return null;
    }

    // 设置错误消息
    protected function setError($option, $defaultMessage)
    {
        $message = $this->getMessage($this->attribute, $option);
        if (is_null($message)) {
            $message = $defaultMessage;
        }
        $this->errors[$option] = $message;
    }

    // 必需验证
    protected function required()
    {
        $value = $this->attributeValue;
        if ($this->isRequired && is_null($value)) {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}不能为空.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

    // 标量类型验证
    protected function scalar()
    {
        $value = $this->attributeValue;
        if (!is_null($value) && !is_scalar($value)) {
            // 文件/图片验证器忽略该类型的验证
            if ($this instanceof \mix\validators\FileValidator) {
                return true;
            }
            // 设置错误消息
            $defaultMessage = "{$this->attribute}不是标量类型.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

    // 无符号验证
    protected function unsigned($param)
    {
        $value = $this->attributeValue;
        if ($param && substr($value, 0, 1) == '-') {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}不能为负数.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

    // 最小数值验证
    protected function min($param)
    {
        $value = $this->attributeValue;
        if (is_numeric($value) && $value < $param) {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}不能小于{$param}.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

    // 最大数值验证
    protected function max($param)
    {
        $value = $this->attributeValue;
        if (is_numeric($value) && $value > $param) {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}不能大于{$param}.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

    // 固定长度验证
    protected function length($param)
    {
        $value = $this->attributeValue;
        if (mb_strlen($value) != $param) {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}长度只能为{$param}位.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

    // 最小长度验证
    protected function minLength($param)
    {
        $value = $this->attributeValue;
        if (mb_strlen($value) < $param) {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}长度不能小于{$param}位.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

    // 最大长度验证
    protected function maxLength($param)
    {
        $value = $this->attributeValue;
        if (mb_strlen($value) > $param) {
            // 设置错误消息
            $defaultMessage = "{$this->attribute}长度不能大于{$param}位.";
            $this->setError(__FUNCTION__, $defaultMessage);
            // 返回
            return false;
        }
        return true;
    }

}
