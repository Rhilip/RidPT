<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/5/2020
 * Time: 10:56 PM
 */

declare(strict_types=1);

namespace Rid\Validators\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class looseChoiceValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Choice) {
            throw new UnexpectedTypeException($constraint, Choice::class);
        }

        if (!\is_array($constraint->choices) && !$constraint->callback) {
            throw new ConstraintDefinitionException('Either "choices" or "callback" must be specified on constraint Choice.');
        }

        if (null === $value) {
            return;
        }

        if ($constraint->multiple && !\is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        if ($constraint->callback) {
            if (!\is_callable($choices = [$this->context->getObject(), $constraint->callback])
                && !\is_callable($choices = [$this->context->getClassName(), $constraint->callback])
                && !\is_callable($choices = $constraint->callback)
            ) {
                throw new ConstraintDefinitionException('The Choice constraint expects a valid callback.');
            }
            $choices = $choices();
        } else {
            $choices = $constraint->choices;
        }

        if ($constraint->multiple) {
            foreach ($value as $_value) {
                if (!\in_array($_value, $choices)) {
                    $this->context->buildViolation($constraint->multipleMessage)
                        ->setParameter('{{ value }}', $this->formatValue($_value))
                        ->setParameter('{{ choices }}', $this->formatValues($choices))
                        ->setCode(Choice::NO_SUCH_CHOICE_ERROR)
                        ->setInvalidValue($_value)
                        ->addViolation();

                    return;
                }
            }

            $count = \count($value);

            if (null !== $constraint->min && $count < $constraint->min) {
                $this->context->buildViolation($constraint->minMessage)
                    ->setParameter('{{ limit }}', $constraint->min)
                    ->setPlural((int) $constraint->min)
                    ->setCode(Choice::TOO_FEW_ERROR)
                    ->addViolation();

                return;
            }

            if (null !== $constraint->max && $count > $constraint->max) {
                $this->context->buildViolation($constraint->maxMessage)
                    ->setParameter('{{ limit }}', $constraint->max)
                    ->setPlural((int) $constraint->max)
                    ->setCode(Choice::TOO_MANY_ERROR)
                    ->addViolation();

                return;
            }
        } elseif (!\in_array($value, $choices)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setParameter('{{ choices }}', $this->formatValues($choices))
                ->setCode(Choice::NO_SUCH_CHOICE_ERROR)
                ->addViolation();
        }
    }
}
