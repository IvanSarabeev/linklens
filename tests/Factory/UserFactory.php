<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Domain\User\Model\ApiToken;
use App\Domain\User\Model\User;
use DateTimeImmutable;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

final class UserFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return User::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'email' => self::faker()->unique()->safeEmail(),
            'apiTokenHash' => ApiToken::hash(ApiToken::generate()),
            'createdAt' => new DateTimeImmutable()
        ];
    }
}
