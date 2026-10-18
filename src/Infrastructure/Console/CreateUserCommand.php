<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Application\User\RegisterUser;
use App\Domain\User\Exception\InvalidEmail;
use App\Domain\User\Exception\UserAlreadyExists;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:user:create', description: 'Create a User and print its API token once')]
final readonly class CreateUserCommand
{
    public function __construct(private RegisterUser $registerUser)
    { }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument(description: 'Email address')]
        string       $email
    ): int
    {
        try {
            [$user, $token] = ($this->registerUser)($email);
        } catch (InvalidEmail $exception) {
            $io->error($exception->getMessage());

            return Command::INVALID;
        } catch (UserAlreadyExists $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $io->success(
            sprintf('Created %s (%s)',
                $user->getEmail(),
                $user->getUuid()->toRfc4122()
            )
        );
        $io->warning('Copy the API token now; it cannot be shown again.');
        $io->writeln('API token (shown once): ' . $token);

        return Command::SUCCESS;
    }

}
