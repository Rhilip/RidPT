<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 4/29/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace Rid\Console\CommandLoader;

use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

class FactoryCommandLoader implements CommandLoaderInterface
{
    private ?array $factories = null;

    /**
     * @param callable[] $factories Indexed by command names
     */
    public function __construct(array $factories)
    {
        $this->factories = $factories;
    }

    public function has(string $name)
    {
        return isset($this->factories[$name]);
    }

    public function get(string $name)
    {
        if (!isset($this->factories[$name])) {
            throw new CommandNotFoundException(sprintf('Command "%s" does not exist.', $name));
        }

        $factory = $this->factories[$name];

        return new $factory();
    }


    public function getNames()
    {
        return array_keys($this->factories);
    }
}
