<?php

declare(strict_types=1);

namespace App\Tests\Functional\Console;

use App\Tests\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class CreateUserCommandTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    public function testCreatesUserAndPrintsToken(): void
    {
        $tester = $this->tester();
        $tester->execute(['email' => 'new@example.com']);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertMatchesRegularExpression('/[0-9a-f]{64}/', $tester->getDisplay());
        self::assertSame(1, UserFactory::count(['email' => 'new@example.com']));
    }

    public function testRejectsDuplicateEmailCaseInsensitively(): void
    {
        $tester = $this->tester(); // boot the kernel before using factories
        UserFactory::createOne(['email' => 'taken@example.com']);

        $tester->execute(['email' => 'Taken@Example.com']);

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
    }

    public function testRejectsInvalidEmail(): void
    {
        $tester = $this->tester();
        $tester->execute(['email' => 'not-an-email']);

        self::assertSame(Command::INVALID, $tester->getStatusCode());
    }

    private function tester(): CommandTester
    {
        $application = new Application(self::bootKernel());

        return new CommandTester($application->find('app:user:create'));
    }
}
