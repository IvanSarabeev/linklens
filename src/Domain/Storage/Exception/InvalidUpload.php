<?php

declare(strict_types=1);

namespace App\Domain\Storage\Exception;

use App\Domain\Shared\DomainException;
use App\Domain\Storage\Model\UploadRejection;

final class InvalidUpload extends DomainException
{
    public function __construct(public readonly UploadRejection $uploadRejection)
    {
        parent::__construct($this->uploadRejection->message());
    }

    public static function because(UploadRejection $reason): self
    {
        return new self($reason);
    }
}
