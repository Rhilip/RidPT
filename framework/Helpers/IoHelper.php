<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 4/29/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace Rid\Helpers;

use Symfony\Component\Console\Style\SymfonyStyle;

class IoHelper
{
    protected static ?SymfonyStyle $io = null;

    /**
     * @return SymfonyStyle|null
     */
    public static function getIo(): ?SymfonyStyle
    {
        return self::$io;
    }

    /**
     * @param SymfonyStyle|null $io
     */
    public static function setIo(?SymfonyStyle $io): void
    {
        self::$io = $io;
    }
}
