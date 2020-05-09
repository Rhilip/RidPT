<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 5/9/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace Rid\Component;

/**
 * It worked as Swoole\Coroutine\Context,
 * However since our application don't support coroutine yet,
 * We should have another context manager to store all request state,
 * To let our service stateless.
 *
 * Class Runtime
 * @package Rid\Component
 */
class Runtime implements \ArrayAccess
{
    private array $context = [];

    public function cleanContext()
    {
        $this->context = [];
    }

    public function offsetExists($offset)
    {
        return isset($this->context[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->context[$offset]) ? $this->context[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->context[] = $value;
        } else {
            $this->context[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->context[$offset]);
    }
}
