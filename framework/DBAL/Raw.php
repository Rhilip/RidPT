<?php
/**
 * @noinspection PhpMissingFieldTypeInspection
 *
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/8/2020
 * Time: 8:00 PM
 */

declare(strict_types=1);

namespace Rid\DBAL;

class Raw
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function __toString()
    {
        return (string)$this->getValue();
    }
}
