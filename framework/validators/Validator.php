<?php

namespace mix\validators;

use mix\base\BaseObject;

/**
 * Validator基类
 * @author 刘健 <coder.liu@qq.com>
 */
class Validator extends BaseObject
{

    // 全部属性
    public $attributes;

    // 当前场景
    protected $_scenario;

    // 验证器类路径
    protected $_validators = [
        'integer'      => '\mix\validators\IntegerValidator',
        'double'       => '\mix\validators\DoubleValidator',
        'alpha'        => '\mix\validators\AlphaValidator',
        'alphaNumeric' => '\mix\validators\AlphaNumericValidator',
        'string'       => '\mix\validators\StringValidator',
        'in'           => '\mix\validators\InValidator',
        'date'         => '\mix\validators\DateValidator',
        'email'        => '\mix\validators\EmailValidator',
        'phone'        => '\mix\validators\PhoneValidator',
        'url'          => '\mix\validators\UrlValidator',
        'compare'      => '\mix\validators\CompareValidator',
        'match'        => '\mix\validators\MatchValidator',
        'call'         => '\mix\validators\CallValidator',
        'file'         => '\mix\validators\FileValidator',
        'image'        => '\mix\validators\ImageValidator',
    ];

    // 错误
    protected $_errors = [];

    // 规则
    public function rules()
    {
        return [];
    }

    // 场景
    public function scenarios()
    {
        return [];
    }

    // 消息
    public function messages()
    {
        return [];
    }

    // 设置当前场景
    public function setScenario($scenario)
    {
        $scenarios = $this->scenarios();
        if (!isset($scenarios[$scenario])) {
            throw new \mix\exceptions\ValidatorException("场景不存在：{$scenario}");
        }
        if (!isset($scenarios[$scenario]['required'])) {
            throw new \mix\exceptions\ValidatorException("场景 {$scenario} 未定义 required 选项");
        }
        if (!isset($scenarios[$scenario]['optional'])) {
            $scenarios[$scenario]['optional'] = [];
        }
        $this->_scenario = $scenarios[$scenario];
    }

    // 验证
    public function validate()
    {
        if (!isset($this->_scenario)) {
            throw new \mix\exceptions\ValidatorException("场景未设置");
        }
        $this->_errors      = [];
        $scenario           = $this->_scenario;
        $scenarioAttributes = array_merge($scenario['required'], $scenario['optional']);
        $rules              = $this->rules();
        $messages           = $this->messages();
        // 判断是否定义了规则
        foreach ($scenarioAttributes as $attribute) {
            if (!isset($rules[$attribute])) {
                throw new \mix\exceptions\ValidatorException("属性 {$attribute} 未定义规则");
            }
        }
        // 验证器验证
        foreach ($rules as $attribute => $rule) {
            if (!in_array($attribute, $scenarioAttributes)) {
                continue;
            }
            $validatorType = array_shift($rule);
            if (!isset($this->_validators[$validatorType])) {
                throw new \mix\exceptions\ValidatorException("属性 {$attribute} 的验证类型 {$validatorType} 不存在");
            }
            $attributeValue = isset($this->attributes[$attribute]) ? $this->attributes[$attribute] : null;
            // 实例化
            $validatorClass           = $this->_validators[$validatorType];
            $validator                = new $validatorClass([
                'isRequired'     => in_array($attribute, $scenario['required']),
                'options'        => $rule,
                'attribute'      => $attribute,
                'attributeValue' => $attributeValue,
                'messages'       => $messages,
                'attributes'     => $this->attributes,
            ]);
            $validator->mainValidator = $this;
            // 验证
            if (!$validator->validate()) {
                // 记录错误消息
                $this->_errors[$attribute] = $validator->errors;
            }
        }
        return empty($this->_errors);
    }

    // 返回全部错误
    public function getErrors()
    {
        return $this->_errors;
    }

    // 返回一条错误
    public function getError()
    {
        $errors = $this->_errors;
        if (empty($errors)) {
            return '';
        }
        $item  = array_shift($errors);
        $error = array_shift($item);
        return $error;
    }

}
