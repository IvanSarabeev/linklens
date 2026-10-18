<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Security;

use App\Domain\User\UserRepository;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface as TUser;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Uid\Uuid;

/** @implements UserProviderInterface<SecurityUser> */
final readonly class SecurityUserProvider implements UserProviderInterface
{
    public function __construct(private UserRepository $userRepository)
    { }

    public function refreshUser(TUser $user): TUser
    {
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return SecurityUser::class === $class;
    }

    public function loadUserByIdentifier(string $identifier): TUser
    {
        $user = Uuid::isValid($identifier)
            ? $this->userRepository->findByUuid(Uuid::fromString($identifier))
            : null;

        return null !== $user
            ? SecurityUser::fromUser($user)
            : throw new UserNotFoundException();
    }


}
