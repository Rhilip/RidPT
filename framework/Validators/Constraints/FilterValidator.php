<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/5/2020
 * Time: 6:35 PM
 */

declare(strict_types=1);

namespace Rid\Validators\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FilterValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Filter) {
            throw new UnexpectedTypeException($constraint, Filter::class);
        }

        if (null !== $constraint->normalizer) {
            $value = ($constraint->normalizer)($value);
        }

        $options = null;
        if (null !== $constraint->options || null !== $constraint->flags) {
            $options = [];
            if (null !== $constraint->options) {
                $options['options'] = $constraint->options;
            }
            if (null !== $constraint->flags) {
                $options['flags'] = $constraint->flags;
            }
        }

        $value = filter_var($value, $constraint->filter, $options);
        if (false === $value) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(Filter::FILTER_FAILS_ERROR)
                ->addViolation();
        }
    }
}
