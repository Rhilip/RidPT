<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/1/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace Rid\Validators;

use Rid\Base\AbstractObject;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractValidator extends AbstractObject
{
    private array $input = [];

    private bool $_success = true;
    private array $_errors = [];

    /**
     * Load Assert Which used by Symfony Validation
     * @return Assert\Collection
     * @see https://symfony.com/doc/current/reference/constraints.html
     */
    abstract protected function loadInputMetadata(): Assert\Collection;

    /**
     * Load Callback MetaData after Symfony Validation
     *
     * @return array
     */
    abstract protected function loadCallbackMetaData(): array;

    /**
     * Which action should we do after validator success,
     * Call it in Controller
     *
     * @return mixed
     */
    abstract public function flush();

    final public function setInput($input)
    {
        $this->input = array_merge($this->input, $input);
    }

    final public function hasInput($input)
    {
        return isset($this->input[$input]);
    }

    /**
     * @param $key
     * @param mixed $default
     * @return mixed
     */
    final public function getInput($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->input;
        }

        return $this->input[$key] ?? $default;
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

    final public function validate($group = null): bool
    {
        $this->resetValidateStatus();

        // Check if input value pass the Symfony Validator
        $validator = container()->get(ValidatorInterface::class);

        $rules = $this->loadInputMetadata();
        $rules->allowExtraFields = true;

        $violations = $validator->validate($this->input, $rules, $group);
        if (count($violations) > 0) {
            $this->_success = false;
            foreach ($violations as $violation) {
                /** @var ConstraintViolationInterface $violation */
                $this->_errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
        }

        // Check other Callback Function inside Object
        if ($this->_success) {
            foreach ($this->loadCallbackMetaData() as $rule) {
                call_user_func([$this, $rule]);
                if (!$this->_success) {
                    break;
                }
            }
        }

        return $this->_success;
    }

    private function resetValidateStatus()
    {
        $this->_success = true;
        $this->_errors = [];
    }

    final protected function buildCallbackFailMsg($path, $message)
    {
        $this->_success = false;
        $this->_errors[$path] = $message;
    }
}
