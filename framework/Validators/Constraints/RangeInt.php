<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/5/2020
 * Time: 10:36 PM
 */

declare(strict_types=1);

namespace Rid\Validators\Constraints;

use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * Use PHP FILTER_VALIDATE_INT to valid if input is int and in a range
 * Used As:
 *    new RangeInt(['min' => 0, 'max' => 50])
 *    new RangeInt(['options' => ['min_range' => 0, 'max_range' => 50]])
 *
 * Class RangeInt
 * @package Rid\Validators\Constraints
 */
class RangeInt extends Filter
{
    public ?int $min = null;
    public ?int $max = null;

    public function __construct($options = null)
    {
        $options['filter'] = FILTER_VALIDATE_INT;
        parent::__construct($options);

        if (null === $this->options) {
            if (!is_int($this->min) || !is_int($this->max)) {
                throw new ConstraintDefinitionException('The options "min" or "max" must be int type.');
            }
            $this->options = ['min_range' => $this->min, 'max_range' => $this->max];
        }
    }

    public function validatedBy()
    {
        return FilterValidator::class;
    }
}
