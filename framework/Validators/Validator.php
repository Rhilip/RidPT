<?php

namespace Mix\Validators;

use Mix\Base\BaseObject;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Validator extends BaseObject
{

    // 全部属性
    public $attributes;

    public static function rule()
    {
        return [];
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $rules = self::rule();
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
        $errors = $validator->validate($this);
        return $errors;
    }
}
