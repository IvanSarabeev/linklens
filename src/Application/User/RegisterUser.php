<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Domain\Shared\DomainException;
use App\Domain\User\Exception\UserAlreadyExists;
use App\Domain\User\Model\ApiToken;
use App\Domain\User\Model\Email;
use App\Domain\User\Model\User;
use App\Domain\User\UserRepository;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;

final readonly class RegisterUser
{
    private const string USER_PREFIX = 'User: ';

    public function __construct(
        private UserRepository  $userRepository,
        private ClockInterface  $clock,
        private LoggerInterface $logger,
    )
    { }

    /**
     * @param string $rawEmail
     * @return array{User, string} the User and its plain token
     */
    public function __invoke(string $rawEmail): array
    {
        try {
            $email = Email::fromString($rawEmail);

            if (null !== $this->userRepository->findByEmail($email->value)) {
                throw new UserAlreadyExists('A user already exists.');
            }

            $plainToken = ApiToken::generate();
            $user = new User($email->value, ApiToken::hash($plainToken), $this->clock->now());
            $this->userRepository->save($user);
        } catch (DomainException $exception) {
            $this->logger->warning(self::USER_PREFIX . 'registration rejected', [
                'reason' => $exception::class
            ]);

            throw $exception;
        }

        $this->logger->info(self::USER_PREFIX . 'registered', [
            'user_id' => $user->getUuid()->toRfc4122()
        ]);

        return [$user, $plainToken];
    }
}
