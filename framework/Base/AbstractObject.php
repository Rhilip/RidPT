<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 5/7/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace Rid\Base;


use DI\Container;

abstract class AbstractObject
{

    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }
}
