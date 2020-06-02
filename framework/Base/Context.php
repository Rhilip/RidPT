<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/2/2020
 * Time: 2:38 PM
 */

declare(strict_types=1);

namespace Rid\Base;

/**
 * It worked as Swoole\Coroutine\Context,
 * However since our application don't support coroutine yet,
 * We should have another context manager to store all request state,
 * To let our service stateless.
 *
 * Class Runtime
 * @package Rid\Base
 */
class Context
{
    private array $context = [];

    public function set(string $id, $value)
    {
        $this->context[$id] = $value;
        return $value;
    }

    public function get(string $id, $default = null)
    {
        return $this->context[$id] ?? $default;
    }

    public function has(string $id)
    {
        return isset($this->context[$id]);
    }

    public function destroy(string $id)
    {
        unset($this->context[$id]);
    }

    public function getOrSet(string $id, $value)
    {
        if (!$this->has($id)) {
            return $this->set($id, $value);
        }
        return $this->get($id);
    }

    public function append(string $id, $value)
    {
        if (!$this->has($id)) {
            $this->context[$id] = [];
            $this->set($id, []);
        }
        $this->context[$id][] = $value;
    }

    public function cleanContext()
    {
        $this->context = [];
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
