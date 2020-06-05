<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/3/2020
 * Time: 5:57 PM
 */

declare(strict_types=1);

namespace Rid\Validators\Constraints;

use Symfony\Component\Validator\Constraints as Assert;

class PositiveOrZeroInt extends Assert\Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Filter(FILTER_VALIDATE_INT),
            new Assert\PositiveOrZero(),
        ];
    }
}
