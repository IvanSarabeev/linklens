<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\User\Model\User;
use Symfony\Component\Uid\Uuid;

interface UserRepository
{
    public function save(User $user): void;

    public function findByUuid(Uuid $uuid): ?User;

    public function findByApiTokenHash(string $hash): ?User;
}
