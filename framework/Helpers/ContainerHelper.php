<?php
/** FIXME don't use HelperFacade
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 4/30/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace Rid\Helpers;

use DI\Container;

class ContainerHelper
{
    protected static Container $_container;

    /**
     * @return Container
     */
    public static function getContainer(): Container
    {
        return self::$_container;
    }

    /**
     * @param Container $container
     */
    public static function setContainer(Container $container): void
    {
        self::$_container = $container;
    }
}
