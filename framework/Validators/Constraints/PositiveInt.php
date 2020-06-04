<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/3/2020
 * Time: 11:03 PM
 */

declare(strict_types=1);

namespace Rid\Validators\Constraints;

use Symfony\Component\Validator\Constraints as Assert;

class PositiveInt extends Assert\Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\Regex('/^\d+$/'),
            new Assert\Positive(),
        ];
    }
}
