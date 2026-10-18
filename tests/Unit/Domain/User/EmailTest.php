<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\User;

use App\Domain\User\Exception\InvalidEmail;
use App\Domain\User\Model\Email;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    public function testNormalises(): void
    {
        self::assertSame('ivan@example.com', Email::fromString('  Ivan@Example.COM ')->value);
    }

    #[DataProvider('invalidEmails')]
    public function testRejectsInvalid(string $raw): void
    {
        $this->expectException(InvalidEmail::class);
        Email::fromString($raw);
    }

    /** @return iterable<string, array{string}> */
    public static function invalidEmails(): iterable
    {
        yield 'empty' => [''];
        yield 'no at sign' => ['not-an-email'];
        yield 'no domain' => ['ivan@'];
        yield 'too long' => [str_repeat('a', 175).'@x.com'];
    }
}
