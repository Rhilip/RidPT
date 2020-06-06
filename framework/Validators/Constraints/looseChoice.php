<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/5/2020
 * Time: 10:56 PM
 */

declare(strict_types=1);

namespace Rid\Validators\Constraints;

use Symfony\Component\Validator\Constraints\Choice;

/**
 * Symfony Choice require strict compare and deprecated the `strict` option.
 *
 * However all our input is string or string[],
 * But the choice may by int[] at sometime.
 *
 * So looseChoice is need in our project.
 *
 * Class looseChoice
 * @package Rid\Validators\Constraints
 * @see https://symfony.com/doc/current/reference/constraints/Choice.html
 */
class looseChoice extends Choice
{
}
