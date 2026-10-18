<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\User\Exception\UserAlreadyExists;
use App\Domain\User\Model\User;
use Symfony\Component\Uid\Uuid;

interface UserRepository
{
    public function save(User $user): void;

    public function findByUuid(Uuid $uuid): ?User;

    /**
     * @param string $email
     * @return User|null
     * @throws UserAlreadyExists when the email is taken (also caught at DB level)
     */
    public function findByEmail(string $email): ?User;

    public function findByApiTokenHash(string $hash): ?User;
}
