<?php

namespace Rid\Validators;

use Rid\Base\BaseObject;

use ReflectionClass;
use ReflectionProperty;
use ReflectionException;

/**
 * Docs: http://www.sirius.ro/php/sirius/validation/
 *
 * Wrapper of Class Validator
 * @package Rid\Validators
 *
 * Any Properties from user post data MUST be public and use function getData() in CallbackRule function
 *                other should be private or protected
 * Any CallbackRule function should be protected
 * Any Flush function should be private
 */
class Validator extends BaseObject
{
    /** @var array Input data */
    private array $_input = [];

    private ?\Sirius\Validation\Validator $_validator;

    private array $_errors = [];

    private bool $_success;

    public function onConstruct()
    {
        $this->_validator = new \Sirius\Validation\Validator;
    }

    /**
     * @return array
     */
    public static function inputRules(): array
    {
        return [];
    }

    public static function callbackRules(): array
    {
        return [];
    }

    public static function defaultData(): array
    {
        return [];
    }

    private function validateCallbackRules()
    {
        foreach (static::callbackRules() as $rule) {
            call_user_func([$this, $rule]);
            if (!$this->_success) {
                break;
            }
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
    final public function setInput($config)
    {
        $this->_input = array_merge($this->_input, $config);
    }

    /**
     * @param $key
     * @param mixed $default
     * @return mixed
     */
    final public function getInput($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->_input;
        }

        return $this->_input[$key] ?? $default;
    }

    final public function __get($name)
    {
        return $this->getInput($name);
    }

    protected function buildDefaultPropAfterValid()
    {
    }

    final protected function releaseDataToProperties()
    {
        try {
            // Get none public properties by reflection
            $reflect = new ReflectionClass($this);
            $no_change_props = array_map(function (ReflectionProperty $property) {
                return $property->name;
            }, $reflect->getProperties(ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_STATIC));

            foreach ($this->_input as $name => $value) {
                if (!in_array($name, $no_change_props)) {
                    $this->$name = $this->getInput($name);
                } else {
                    $this->buildCallbackFailMsg('harking', 'User post may hack.');
                    return;
                }
            }
        } catch (ReflectionException $e) {
            $this->buildCallbackFailMsg('internal', 'Release user upload data error when reflection.');
            return;
        }
    }

    public function validate(): bool
    {
        $this->_input = array_merge(static::defaultData(), $this->_input);

        // validate rules in static::inputRules()
        $this->_validator->add(static::inputRules());
        $this->_success = $this->_validator->validate($this->_input);
        $this->_errors = $this->_validator->getMessages();

        if ($this->_success) {
            $this->validateCallbackRules();
        } // Valid callback rules
        if ($this->_success) {
            $this->releaseDataToProperties();
        } // release validate data to class properties which is type if public when valid success

        $this->buildDefaultPropAfterValid();

        return $this->_success;
    }

    public function getErrors(): array
    {
        $out_error = [];
        foreach ($this->_errors as $key => $error) {
            $msg = '';
            if (is_array($error)) {
                foreach ($error as $value) {
                    $msg .= $value . '; ';
                }
            } else {
                $msg = $error;
            }

            $out_error[] = "$key : $msg";
        }
        return $out_error;
    }

    public function getError(): string
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
