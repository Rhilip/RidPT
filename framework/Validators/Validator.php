<?php

namespace Rid\Validators;

use Rid\Base\BaseObject;
use Rid\Http\UploadFile;

/**
 * Docs: http://www.sirius.ro/php/sirius/validation/
 *
 * Wrapper of Class Validator
 * @package Rid\Validators
 */
class Validator extends BaseObject
{

    /** @var array Input data */
    protected $_data;

    /** @var \Sirius\Validation\Validator */
    protected $_validator;

    /** @var array */
    protected $_errors = [];

    /** @var boolean */
    protected $_success;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->_validator = new \Sirius\Validation\Validator;
        $this->_validator->add(static::inputRules());
    }

    public static function inputRules()
    {
        return [];
    }

    public static function callbackRules() {
        return [];
    }


    private function validateCallbackRules()
    {
        foreach (static::callbackRules() as $rule) {
            call_user_func([$this, $rule]);
            if (!$this->_success) break;
        }
    }

    protected function buildCallbackFailMsg($field, $msg)
    {
        $this->_success = false;
        $this->_errors[$field] = $msg;
    }

    /** Storage Data in $_data and assign all as object's attribute.
     * @param $config
     */
    public function setData($config)
    {
        $this->_data = $config;
        foreach ($config as $name => $value) {
            $this->$name = $value;
        }
    }

    public function setFileData($config) {
        $this->_data += $config;
        foreach ($config as $name => $value) {
            $this->$name = UploadFile::newInstanceByName($name);
        }
    }

    public function validate()
    {
        $this->_success = $this->_validator->validate($this->_data);
        $this->_errors = $this->_validator->getMessages();

        if ($this->_success) {
            $this->validateCallbackRules();
        }

        return $this->_success;
    }

    public function getErrors()
    {
        $out_error = [];
        foreach ($this->_errors as $key => $error) {
            $msg = '';
            if (is_array($error)) {
                foreach ($error as $value) $msg .= $value . '; ';
            } else {
                $msg = $error;
            }

            $out_error[] = "$key : $msg";
        }
        return $out_error;
    }

    public function getError()
    {
        if (empty($this->_errors)) {
            return '';
        }
        return $this->getErrors()[0];
    }
}
