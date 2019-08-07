<?php

namespace Rid\Validators;

use Rid\Base\BaseObject;
use Rid\Http\UploadFile;

/**
 * Docs: http://www.sirius.ro/php/sirius/validation/
 *
 * Wrapper of Class Validator
 * @package Rid\Validators
 *
 *
 * Any Properties from user post data MUST be public and use function getData() in CallbackRule function
 *                other should be private or protected
 * Any CallbackRule function should be protected
 * Any Flush function should be private
 */
class Validator extends BaseObject
{

    /** @var array Input data */
    protected $_data = [];
    protected $_file_data_name = [];

    /** @var \Sirius\Validation\Validator */
    protected $_validator;

    /** @var array */
    protected $_errors = [];

    /** @var boolean */
    protected $_success;

    protected $_autoload_data = false;
    protected $_autoload_data_from = [];

    public function onConstruct()
    {
        $this->_validator = new \Sirius\Validation\Validator;
    }

    public static function inputRules()
    {
        return [];
    }

    public static function callbackRules()
    {
        return [];
    }

    public static function defaultData()
    {
        return [];
    }

    private function validateCallbackRules()
    {
        foreach (static::callbackRules() as $rule) {
            call_user_func([$this, $rule]);
            if (!$this->_success) break;
        }
    }

    final protected function buildCallbackFailMsg($field, $msg)
    {
        $this->_success = false;
        $this->_errors[$field] = $msg;
    }

    /** Storage Data in $_data for valid
     * @param $config
     */
    final public function setData($config)
    {
        $this->_data = array_merge($this->_data, $config);
    }

    final public function setFileData($config)
    {
        $this->setData($config);
        $this->_file_data_name += array_keys($config);
    }

    /**
     * @param $key
     * @param mixed $default
     * @return mixed|UploadFile
     */
    final public function getData($key = null,$default = null)
    {
        if (is_null($key)) return $this->_data;

        if (in_array($key, $this->_file_data_name))
            return new UploadFile($this->_data[$key]);

        return $this->_data[$key] ?? $default;
    }

    /**
     * rewrite data from user for valid by change $this->_data
     */
    protected function buildDefaultDataForValid()
    {
        \Rid::setDefault($this->_data, static::defaultData());
    }

    protected function buildDefaultPropBeforeValid()
    {
    }

    protected function buildDefaultPropAfterValid()
    {
    }

    final protected function releaseDataToProperties()
    {
        $this->buildDefaultPropBeforeValid();

        try {
            // Get public properties by reflection
            $reflect = new \ReflectionClass($this);
            $public_props = array_keys($reflect->getProperties(\ReflectionProperty::IS_PUBLIC));
            $no_change_props = array_keys($reflect->getProperties(\ReflectionProperty::IS_PRIVATE |
                \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_STATIC));

            foreach ($this->_data as $name => $value) {
                if (in_array($name, $public_props)) {
                    $this->$name = $this->getData($name);
                } elseif (in_array($name, $no_change_props)) {
                    $this->buildCallbackFailMsg('harking', 'User post may hack.');
                    return;
                }
            }
        } catch (\ReflectionException $e) {
            $this->buildCallbackFailMsg('internal', 'Release user upload data error when reflection.');
            return;
        }

        $this->buildDefaultPropAfterValid();
    }

    private function autoloadDataFromRequests()
    {
        if ($this->_autoload_data) {
            if (in_array('get', $this->_autoload_data_from)) $this->setData(app()->request->get());
            if (in_array('post', $this->_autoload_data_from)) $this->setData(app()->request->post());
            if (in_array('files', $this->_autoload_data_from)) $this->setFileData(app()->request->files());
        }
    }

    public function validate()
    {
        $this->autoloadDataFromRequests();
        $this->buildDefaultDataForValid();

        // validate rules in static::inputRules()
        $this->_validator->add(static::inputRules());
        $this->_success = $this->_validator->validate($this->_data);
        $this->_errors = $this->_validator->getMessages();

        if ($this->_success) $this->validateCallbackRules(); // Valid callback rules
        if ($this->_success) $this->releaseDataToProperties(); // release validate data to class properties which is type if public when valid success

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

    public function flush()
    {
        throw new \RuntimeException('No flush function exist');
    }


}
