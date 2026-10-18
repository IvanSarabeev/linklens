<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Security;

use App\Domain\User\Model\User;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class SecurityUser implements UserInterface
{
    public function __construct(
        private string $uuid,
        private string $email,
        private array $roles
    )
    { }

    public static function fromUser(User $user): self
    {
        $id = $user->getUuid()->toRfc4122();
        assert('' !== $id);

        return new self($id, $user->getEmail(), $user->getRoles());
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getUserIdentifier(): string
    {
        return $this->uuid;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

}
