<?php

declare(strict_types=1);

namespace App\Domain\User\Model;

final class ApiToken
{
    public static function generate(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function hash(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }
}
