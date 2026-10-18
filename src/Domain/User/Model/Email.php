<?php

declare(strict_types=1);

namespace App\Domain\User\Model;

use App\Domain\User\Exception\InvalidEmail;

final readonly class Email
{
    public function __construct(public string $value)
    { }

    public static function fromString(string $rawValue): self
    {
        $email = mb_strtolower(trim($rawValue));

        if (mb_strlen($email) > 180 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidEmail('Not a valid email address.');
        }

        return new self($email);
    }
}
