<?php

declare(strict_types=1);

namespace App\Domain\Storage;

use App\Domain\Storage\Model\FilePurpose;
use App\Domain\Storage\Model\StoredFile;
use Symfony\Component\Uid\Uuid;

interface StoredFileRepositoryInterface
{
    public function save(StoredFile $file): void;

    public function findById(Uuid $uuid): ?StoredFile;

    public function findDuplicate(Uuid $ownerId, string $sha256, FilePurpose $purpose): ?StoredFile;

    /** return list<StoredFile> newest first */
    public function findByOwner(Uuid $ownerId, int $limit = 50): array;
}
