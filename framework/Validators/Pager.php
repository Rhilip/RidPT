<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/6/2019
 * Time: 6:51 PM
 */

namespace Rid\Validators;


class Pager extends Validator
{
    use PagerTrait;

    public static $DEFAULT_PAGE = 1;
    public static $DEFAULT_LIMIT = 50;
    public static $MAX_LIMIT = 50;
    public static $DATA_SOURCE = 'remote';
}
