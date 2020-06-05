<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/5/2020
 * Time: 6:33 PM
 */

declare(strict_types=1);

namespace Rid\Validators\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class Filter which allow you to use raw php filter_var
 *
 * Used As:
 *  - new Filter(FILTER_VALIDATE_INT) and it works like filter_var($var, FILTER_VALIDATE_INT)
 *  - or
 *      new Filter([
 *          'filter' => FILTER_VALIDATE_BOOLEAN,
 *          'options' => ['default' => 1, 'min_range' => 0, 'max_range' => 50],
 *          'flags' => FILTER_FLAG_ALLOW_HEX
 *      ])
 *    and it works like
 *      filter_var($var, FILTER_VALIDATE_BOOLEAN, [
 *          'options' => ['default' => 1, 'min_range' => 0, 'max_range' => 50],
 *          'flags' => FILTER_FLAG_ALLOW_HEX
 *      ])
 *
 * However, the 'default' options and Sanitize filters is not work,
 * Since we can't get the default or sanitize value from Violations.
 *
 * So though this constraint accept all filter id,
 * The only allowed is Validate filters, and FILTER_CALLBACK
 *
 * @package Rid\Validators\Constraints
 * @see https://www.php.net/manual/en/filter.filters.php
 */
class Filter extends Constraint
{
    const FILTER_FAILS_ERROR = 'dab7d9c4-88f3-4556-adf4-fb8de75640d7';


    public $filter = FILTER_DEFAULT;
    public $flags = null;
    public $options = null;

    public $normalizer;

    public string $message = 'This value is not valid.';

    public function __construct($options = null)
    {
        parent::__construct($options);
        if (!in_array($this->filter, self::getAllFilters())) {
            throw new ConstraintDefinitionException('The option "filter" must be one of "Validate filters".');
        }

        if (null !== $this->normalizer && !\is_callable($this->normalizer)) {
            throw new InvalidArgumentException(sprintf('The "normalizer" option must be a valid callable ("%s" given).', get_debug_type($this->normalizer)));
        }
    }

    private static function getAllFilters(): array
    {
        return array_map(function ($filter_name) {
            return filter_id($filter_name);
        }, filter_list());
    }

    public function getDefaultOption()
    {
        return 'filter';
    }
}
