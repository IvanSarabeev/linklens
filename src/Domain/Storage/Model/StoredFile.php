<?php

declare(strict_types=1);

namespace App\Domain\Storage\Model;

use DateInterval;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'stored_file')]
#[ORM\Index(name: 'idx_store_file_dedupe', columns: ['owner_id', 'sha256', 'purpose'])]
class StoredFile
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $uuid;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    private string $storageKey;

    public function __construct(
        #[ORM\Column(type: UuidType::NAME)]
        private readonly Uuid        $ownerId,
        #[ORM\Column(length: 32, enumType: FilePurpose::class)]
        private readonly FilePurpose       $purpose,
        #[ORM\Column(length: 255)]
        private readonly string             $originalName,
        #[ORM\Column(length: 127)]
        private readonly string             $mimeType,
        #[ORM\Column]
        private readonly int                $sizeBytes,
        #[ORM\Column(name: 'sha256', length: 64)]
        private readonly string             $sha256,
        #[ORM\Column]
        private readonly DateTimeImmutable  $createdAt,
        #[ORM\Column(nullable: true)]
        private readonly ?DateTimeImmutable $expiresAt,
    )
    {
        $this->uuid = Uuid::v7();
        $this->storageKey = sprintf(
            '%s/%s.%s',
            $this->ownerId->toRfc4122(),
            $this->uuid->toRfc4122(),
            $this->extension()
        );
    }

    public static function importSource(
        Uuid $ownerId,
        OriginalFilename $name,
        string $mimeType,
        int $sizeBytes,
        string $sha256,
        DateTimeImmutable $now,
    ): self
    {
        return new self(
            $ownerId,
            FilePurpose::IMPORT_SOURCE,
            $name->value,
            $mimeType,
            $sizeBytes,
            $sha256,
            $now,
            $now->add(new DateInterval('P30D'))
        );
    }

    public function extension(): string
    {
        return strtolower(pathinfo($this->originalName, PATHINFO_EXTENSION));
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getOwnerId(): Uuid
    {
        return $this->ownerId;
    }

    public function getPurpose(): FilePurpose
    {
        return $this->purpose;
    }

    public function getStorageKey(): string
    {
        return $this->storageKey;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getSizeBytes(): int
    {
        return $this->sizeBytes;
    }

    public function getSha256(): string
    {
        return $this->sha256;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }
}
