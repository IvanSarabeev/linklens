<?php

declare(strict_types=1);

namespace App\Domain\User\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'app_user')]
class User
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    public function __construct(
        #[ORM\Column(length: 180, unique: true)]
        private readonly string            $email,
        #[ORM\Column(length: 64, unique: true)]
        private readonly string            $apiTokenHash,
        #[ORM\Column]
        private readonly DateTimeImmutable $createdAt,
        #[ORM\Column(type: 'json')]
        private readonly array             $roles = ['ROLE_USER'],
    )
    {
        $this->id = Uuid::v7();
    }

    public function getUuid(): Uuid
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getApiTokenHash(): string
    {
        return $this->apiTokenHash;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }
}
