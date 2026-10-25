<?php

declare(strict_types=1);

namespace App\Application\Storage;

use App\Domain\Storage\Model\StoredFile;

final readonly class UploadResult
{
    public function __construct(public StoredFile $file, public bool $isDuplicate)
    { }
}
