<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Security;

use App\Domain\User\Model\ApiToken;
use App\Domain\User\UserRepository;
use SensitiveParameter;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

final readonly class ApiTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(private UserRepository $userRepository)
    { }

    public function getUserBadgeFrom(#[SensitiveParameter] string $accessToken): UserBadge
    {
        $user = $this->userRepository->findByApiTokenHash(ApiToken::hash($accessToken))
            ?? throw new BadCredentialsException('Invalid token.');

        $securityUser = SecurityUser::fromUser($user);

        return new UserBadge($securityUser->getUserIdentifier(), static fn() => $securityUser);
    }
}
