<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/3/2020
 * Time: 5:57 PM
 */

declare(strict_types=1);

namespace Rid\Validators\Compound;

use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints as Assert;

class Id extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\Regex('/^\d+$/'),
            new Assert\PositiveOrZero(),
        ];
    }
}
