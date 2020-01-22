<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 1/22/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Entity\User;

interface AbstractUserInterface
{
    public function getId(): int;

    public function getUsername(): string;

    public function getEmail(): string;

    public function getClass(): int;

    public function getStatus(): string;

    public function getAvatar(array $opts = []): string;
}
