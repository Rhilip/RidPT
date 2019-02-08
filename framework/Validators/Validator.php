<?php

namespace Rid\Validators;

use Rid\Base\BaseObject;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Docs: https://symfony.com/doc/current/reference/constraints.html
 *
 * Class Validator
 * @package Rid\Validators
 */
class Validator extends BaseObject
{
    /**  @var \Symfony\Component\Validator\ConstraintViolationListInterface */
    private $_errors;

    public static function rules()
    {
        return [];
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $rules = static::rules();
        foreach ($rules as $property => $constraints) {
            $metadata->addPropertyConstraints($property, $constraints);
        }
    }

    public function importAttributes($config)
    {
        foreach ($config as $name => $value) {
            $this->$name = $value;
        }
    }

    public function validate()
    {
        $validator = Validation::createValidatorBuilder()
            ->addMethodMapping('loadValidatorMetadata')
            ->getValidator();
        $this->_errors = $validator->validate($this);
        return $this->_errors;
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function getError()
    {
        $errors = $this->_errors;
        if (empty($errors)) {
            return '';
        }

        return $errors->get(0);
    }
}
