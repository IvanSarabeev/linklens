<?php

declare(strict_types=1);

namespace App\Domain\Storage\Model;

final readonly class OriginalFilename
{
    public function __construct(public string $value)
    {
    }

    public static function fromClient(string $raw): self
    {
        $name = basename(str_replace('\\', '/', $raw));
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? '';
        $name = mb_substr(trim($name), 0, 255);

        return new self('' === $name ? 'upload' : $name);
    }

    public function extension(): string
    {
        return strtolower(pathinfo($this->value, PATHINFO_EXTENSION));
    }
}
